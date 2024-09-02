import os
import sys
import asyncio
import logging
from langchain_community.embeddings.ollama import OllamaEmbeddings
from langchain_community.vectorstores.chroma import Chroma
from langchain.prompts import ChatPromptTemplate
from langchain_community.llms.ollama import Ollama
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from openai import OpenAI

logger = logging.getLogger(__name__)

class StbotExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()
        self.PROMPT_TEMPLATE = """
            僅根據以下提供的內容回答問題：

            {context}

            ---

            僅根據以上提供的內容回答問題：{question}
        """
        self.MERGE_TEMPLATE = """
            Information:
            {first}
            {second}
            ---
            With the given information, write a one paragraph summary with markdown format in Traditional Chinese. 
            Please remember to use Traditional Chinese.
        """
        self.client = OpenAI(
            base_url = 'http://localhost:11434/v1',
            api_key='ollama', # required, but unused
        )

    def extend_arguments(self, parser):
        parser.add_argument('--delay', type=int, default=0.02, help='Inter-token delay')

    def setup(self):
        self.stop = False

    def get_embedding_function(self):
        embeddings = OllamaEmbeddings(model="shaw/dmeta-embedding-zh:latest")
        return embeddings
    
    def query_rag(self, query_text: str, db_path: str):
        embedding_function = self.get_embedding_function()
        db = Chroma(persist_directory=db_path, embedding_function=embedding_function)
        results = db.similarity_search_with_score(query_text, k=10)

        context_text = "\n\n---\n\n".join([doc.page_content for doc, _ in results])
        prompt_template = ChatPromptTemplate.from_template(self.PROMPT_TEMPLATE)
        prompt = prompt_template.format(context=context_text, question=query_text)

        response = self.client.chat.completions.create(
            model="jcai/llama3-taide-lx-8b-chat-alpha1:Q4_K_M",
            messages=[
                {"role": "system", "content": "You are a helpful assistant."},
                {"role": "user", "content": prompt}
            ]
        )
        
        return response.choices[0].message.content

    async def merge(self, response_1, response_2):
        prompt_template = ChatPromptTemplate.from_template(self.MERGE_TEMPLATE)
        prompt = prompt_template.format(first = response_1, second = response_2)
        
        response = self.client.chat.completions.create(
            model = "llama3.1",
            messages = [
                {"role": "system", "content": "You are a helpful assistant."},
                {"role": "user", "content": prompt}
            ],
            stream = True
        )
        
        for chunk in response:
            if hasattr(chunk.choices[0].delta, 'content'):
                yield chunk.choices[0].delta.content
        
    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        try:
            self.setup()
            userinput = history[-1]['content'].strip()
            # summarized RAG
            summarized = self.query_rag(userinput, "./summary")
            # original RAG
            original = self.query_rag(userinput, "./original")
            # merge two results
            async for chunk in self.merge(summarized, original):
                yield chunk
                if self.stop:
                    return
                
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield str(e)
        finally:
            logger.debug("finished")

    async def abort(self):
        self.stop = True
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = StbotExecutor()
    executor.run()
