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
from kuwa.client import KuwaClient

logger = logging.getLogger(__name__)

lorem = """Hello, I am a language model nice to meet you...etc. xD
<<<WARNING>>>
This is a test warning
This is the second test warning
<<</WARNING>>>
Warning can be outputted in the middle
<<<WARNING>>>
Warning 2, hi
<<</WARNING>>>
End of simulated text output
"""

class DummyExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()
        self.PROMPT_TEMPLATE = """
            僅根據以下提供的內容回答問題：

            {context}

            ---

            僅根據以上提供的內容回答問題：{question}
        """

    def extend_arguments(self, parser):
        parser.add_argument('--delay', type=int, default=0.02, help='Inter-token delay')

    def setup(self):
        self.stop = False
        self.gemini = KuwaClient(
            model='geminipro', auth_token=''
        )
        self.taide = KuwaClient(
            model = 'taide-4bit', auth_token=''
        )
        # self.llama = KuwaClient(
        #     model = 'llama3', auth_token=''
        # )

    def get_embedding_function(self):
        embeddings = OllamaEmbeddings(model="shaw/dmeta-embedding-zh:latest")
        return embeddings
    
    def query_rag(self, query_text: str, db_path: str):
        embedding_function = self.get_embedding_function()
        db = Chroma(persist_directory=db_path, embedding_function=embedding_function)
        results = db.similarity_search_with_score(query_text, k=10)

        context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
        prompt_template = ChatPromptTemplate.from_template(self.PROMPT_TEMPLATE)
        prompt = prompt_template.format(context=context_text, question=query_text)

        # msg = [
        #         {"role":"user", "content": prompt}
        # ]

        # return msg
        
        model = Ollama(model="jcai/llama3-taide-lx-8b-chat-alpha1:Q4_K_M")
        response_text = model.invoke(prompt)
        formatted_response = f"Response: {response_text}"
        # print("\n"+formatted_response)
        return response_text

    def merge(self, response_1, response_2):
        PROMPT_TEMPLATE = """
        merge the following two parts of text into one in markdown format:
        First part:
        {first}
        Second part:
        {second}
        """
        prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE)
        prompt = prompt_template.format(first = response_1, second = response_2)
        response_text = Ollama(model="llama3.1").invoke(prompt)
        return response_text
    
    # Runs everytime chat is requested
    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        try:
            self.setup()
            userinput = history[-1]['content'].strip()
            
            summarized = self.query_rag(userinput, "./summary")
            
            original = self.query_rag(userinput, "./original")

            final = self.merge(summarized, original)

            yield(final)
            
            # async for chunk in self.taide.chat_complete(messages = summarized):
            #     yield chunk
            #     if self.stop:
            #         return
            
            # summarized = query_rag(query, "./summary")
            
            # msg = [
            #     {"role":"user", "content": f"請重複 \"{userinput}\" 三次"}
            # ]
            
            # async for chunck in self.taide.chat_complete(messages = msg):
            #     yield chunck
            #     if self.stop:
            #         return
                
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
    executor = DummyExecutor()
    executor.run()
