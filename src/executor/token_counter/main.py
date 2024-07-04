import os
import sys
import asyncio
import logging
import json
import tiktoken
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile

logger = logging.getLogger(__name__)

class TokenCounterExecutor(LLMExecutor):
    
    supported_tokenizer = ['openai']

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        parser.add_argument('--tokenizer', default='openai', help='The tokenizer to use.')

    def setup(self):
        self.tokenizer_name = self.args.tokenizer

    def openai_num_tokens_from_messages(self, messages):
        """
        Return the number of tokens used by a list of messages.
        Reference: https://cookbook.openai.com/examples/how_to_count_tokens_with_tiktoken
        """
        encoding = tiktoken.get_encoding("cl100k_base")
        
        # Fixed value for nowadays GPT-3.5/4
        tokens_per_message = 3
        tokens_per_name = 1
        
        num_tokens = 0
        for message in messages:
            num_tokens += tokens_per_message
            for key, value in message.items():
                num_tokens += len(encoding.encode(value))
                if key == "name":
                    num_tokens += tokens_per_name
        num_tokens += 3  # every reply is primed with <|start|>assistant<|message|>
        return num_tokens

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        try:
            tokenizer = modelfile.parameters["tokenizer_"].get("name", self.tokenizer_name)
            if tokenizer not in self.supported_tokenizer:
                raise ValueError(f"Tokenizer {tokenizer} not supported. Supported value are {self.supported_tokenizer}")
            if tokenizer == 'openai':
                yield f"{self.openai_num_tokens_from_messages(history[-1:])} tokens"
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield str(e)
        finally:
            logger.debug("finished")

    async def abort(self):
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = TokenCounterExecutor()
    executor.run()