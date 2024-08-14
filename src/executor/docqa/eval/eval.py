import pandas as pd
import os
import google.generativeai as genai
import json
import time
from typing import List
import re
from datasets import Dataset
from ragas import evaluate
from ragas.metrics import faithfulness, answer_correctness

API_KEY = ''

class Eval:
    @staticmethod
    def save_to_json(data, filename):
        with open(filename, 'w') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)

    @staticmethod
    def read_csv_as_text(file_path, num_rows=None):
        df = pd.read_csv(file_path, nrows=num_rows)
        text = df.to_csv(index=False)
        return text

    @staticmethod
    def read_document(file_path):
        with open(file_path, 'r', encoding='utf-8') as file:
            content = file.read()
        return content

    @staticmethod
    def eval_retriever(question, relevant_chunks):
        genai.configure(api_key=API_KEY)
        model = genai.GenerativeModel('gemini-pro')
        yes_chunks = []
        no_chunks = []
        error_chunks = []
        for chunk in relevant_chunks:
            prompt = f"""
            提問:{question}
            出處:{chunk}
            請問出處的資料有沒有跟提問的問題有關聯性? 請用只使用'y' 或是 'n' 來回答，不要包含任何解釋
            """
            while(True):
                try:
                    response = model.generate_content(prompt)
                    break
                except Exception as E:
                    print("\033[1,32m Quota exhausted. Waiting before retrying...\033[0,0m")
                    time.sleep(1)
            response_text = response.text.strip()
            if response_text == 'y':
                yes_chunks.append(chunk)
            elif response_text == 'n':
                no_chunks.append(chunk)
            else:
                error_chunks.append({"chunk": chunk, "response": response_text})

        yes_length = len(yes_chunks)
        accuracy = yes_length / (yes_length + len(no_chunks) + len(error_chunks))
        retriever_result = {
            "question": question,
            "y": yes_chunks,
            "n": no_chunks,
            "error": error_chunks,
            "accuracy": accuracy
        }
        # Eval.save_to_json(retriever_result, 'retriever_result.json')
        return retriever_result

    @staticmethod
    def eval_end2end(question, answer):
        genai.configure(api_key=API_KEY)
        model = genai.GenerativeModel('gemini-pro')

        prompt = f"""
        問題:{question}
        答案:{answer}
        請問答案有沒有回答到問題? 請用只使用'y' 或是 'n' 來回答，不要包含任何解釋
        """

        while(True):
            try:
                response = model.generate_content(prompt)
                break
            except Exception as E:
                print("\033[1,32m Quota exhausted. Waiting before retrying...\033[0,0m")
                time.sleep(1)
        
        response_text = response.text.strip()
        retriever_result = {
            "question": question,
            "answer": answer,
            "related": response_text
        }
    
        return retriever_result

    @staticmethod
    def generate_question(text: str or list) -> list:
        genai.configure(api_key=API_KEY)
        model = genai.GenerativeModel('gemini-pro')

        if isinstance(text, list):
            text_string = " ".join(text)
        else:
            text_string = text

        prompt = f"請閱讀以下文章並且使用一個 \"- \" 做區分跟列出相關問題，最多隨機產生10個問題，請只包含問題不包含其他不必要資訊: {text_string}"

        try:
            response = model.generate_content(prompt)
        except Exception as E:
            print("\033[1,32m Quota exhausted. Waiting before retrying...\033[0,0m")
            time.sleep(1)
            return E  

        pattern = r"- (.*)"
        strings = re.findall(pattern, response.text, flags=re.MULTILINE)
        return strings

    @staticmethod
    def generate_answer(question, relevant_chunks):
        genai.configure(api_key=API_KEY)
        model = genai.GenerativeModel('gemini-pro')
        prompt = f"""
        提問:{question}
        出處:{relevant_chunks}
        請使用出處的資料來回答提問，並且用"From:"列出用來回答提問的出處
        """
        while(True):
            try:
                response = model.generate_content(prompt)
                break
            except Exception as E:
                print("\033[1,32m Quota exhausted. Waiting before retrying...\033[0,0m")
                time.sleep(1)
        return response.text

    @staticmethod
    def yield_retriever(data):
        """
        Yields information from the provided JSON data in a formatted manner.

        Args:
            data: A JSON object containing questions, their accuracies, and relevant/irrelevant chunks.

        Yields:
            A formatted string representing the question, accuracy, and chunks.
        """

        result_string = ""
        for question_data in data['questions']:
            question = question_data['question']
            accuracy = f"Accuracy: {question_data['accuracy']:.2f}"
            relevant_chunks = Eval.extract_page_content(question_data['y'])
            irrelevant_chunks = Eval.extract_page_content(question_data['n'])

            formatted_relevant_chunks = Eval.format_chunks(relevant_chunks)
            formatted_irrelevant_chunks = Eval.format_chunks(irrelevant_chunks)

            result_string += f"\nQuestion: {question}\nAccuracy: {accuracy}\n\nRelevant Chunks:\n{formatted_relevant_chunks}\n\nIrrelevant Chunks:\n{formatted_irrelevant_chunks}\n\n"

        return result_string

    @staticmethod
    def yield_end2end(data):
    
        question = data['question']
        answer = data['answer']
        related = data['related']
        return f"""
        Question: {question}
        Answer:  {answer}
        Is related: {related}
        """

    def format_chunks(chunks):
        return "\n".join(f"{index + 1}. {chunk}" for index, chunk in enumerate(chunks))

    @staticmethod
    def extract_page_content(relevant_chunks):
        """Extracts page content from a list of Document objects.

        Args:
            relevant_chunks: A list of Document objects.

        Returns:
            A list of page content strings.
        """

        page_contents = []
        for document in relevant_chunks:
            page_contents.append(document.page_content)
        return page_contents

    @staticmethod
    def clean_llm_response(response):
        try:
            # Find the start and end positions of the JSON object
            start_marker = '{'
            end_marker = '}'
            
            start_index = response.index(start_marker)
            end_index = response.rindex(end_marker) + 1
            
            # Extract the JSON part
            json_str = response[start_index:end_index]
            
            # Parse the JSON string
            parsed_json = json.loads(json_str)
            
            return parsed_json
        
        except ValueError as e:
            raise ValueError(f"Error processing the response: {e}")

    def filter_questions(parsed_json):
        for item in parsed_json:
            text = item['text']
            questions = item['questions']
            
            # Filter out questions where the answer does not match the text word by word
            filtered_questions = [q for q in questions if q['answer'] in text.split()]
            
            # Update the questions list with filtered questions
            item['questions'] = filtered_questions
        
        return parsed_json

    @staticmethod
    def ragas_eval(data_sample):
        dataset = Dataset.from_dict(data_sample)
        score = evaluate(dataset, metrics=[faithfulness, answer_correctness])
        df = score.to_pandas()
        df.to_csv('score.csv', index=False)

    @staticmethod
    def generate_questions_RAG(context):
        genai.configure(api_key=API_KEY)
        model = genai.GenerativeModel('gemini-pro')
    
        prompt = f"""
        使用所提供的上下文產生至少一個可以直接從文字逐字回答的問題。
        問題不能簡單，答案也不能太短，必須逐字從文字中回答。
        使用JSON格式來回答 Context。
        - 'questions': list of str
        - 'ground_truth': The ground truth answer to the questions that you will answer word by word from the text
        
        Here is the Context: {context}
        """
        while(True):
            try:
                response = model.generate_content(prompt)
                break
            except Exception as E:
                print("\033[1,32m Quota exhausted. Waiting before retrying...\033[0,0m")
                time.sleep(1)
        return response.text



def main():
    # file_path = r'src/executor/docqa/src/AS-AIGFAQ.csv'
    # text = Eval.read_csv_as_text(file_path, num_rows=2)

    # questions = Eval.generate_question(text)
    # # response = Eval.model.count_tokens(text)
    # # print(f"Prompt Token Count: {response.total_tokens}")

    # # if response.total_tokens >= 200000:
    # #     print("Please trim the file before pass in...")

    # for question in questions:
    #     answer = "sample answer"  # You need to provide an actual answer here
    #     Eval.eval_end2end(question, answer)




    # print(Eval.clean_llm_response(generate_questions(context)))

    #print(Eval.parse_and_filter_questions(jason_str))


    return None

if __name__ == '__main__':
    main()
