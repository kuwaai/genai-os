import torch, accelerate
import logging
import asyncio
from transformers import AutoModelForCausalLM, AutoConfig, AutoTokenizer, \
                         StoppingCriteria, StoppingCriteriaList, \
                         pipeline, GenerationConfig
from .taide_llm import TaideLlm

logger = logging.getLogger(__name__)

class StopOnTokens(StoppingCriteria):
    def __init__(self, tokenizer: AutoTokenizer):
        stop_list = ['<s>', '</s>', '[INST]', '\nQuestion:', "[INST: ]"]
        to_token_id = lambda x: torch.LongTensor(tokenizer(x)['input_ids']).to('cuda')
        self.stop_token_ids = map(to_token_id, stop_list)
    
    def __call__(self, input_ids: torch.LongTensor, scores: torch.FloatTensor, **kwargs) -> bool:
        for stop_ids in self.stop_token_ids:
            if torch.all(input_ids[0][-len(stop_ids):] == stop_ids):
                return True
        return False

class LocalTaideLlm(TaideLlm):
    def __init__(self,
                 model_path = '/llm/llama2-7b-chat-b5.0.0',
                 ):
        super(LocalTaideLlm, self).__init__()
        
        model = AutoModelForCausalLM.from_pretrained(
            model_path,
            config=AutoConfig.from_pretrained(model_path),
            device_map="auto",
            torch_dtype=torch.float16
        )
        model.eval()

        self.model_tokenizer = AutoTokenizer.from_pretrained(model_path)

        self.pipe = pipeline(
            model=model,
            tokenizer=self.model_tokenizer,
            return_full_text=False,
            task='text-generation',
            stopping_criteria=StoppingCriteriaList([StopOnTokens(self.model_tokenizer)]),
            max_length=4096,
            # max_new_tokens=2048,
            # num_beams=2, early_stopping=True, # Beam search
            do_sample=True, temperature=0.2, top_p=0.95, # Top-p (nucleus) sampling
            # penalty_alpha=0.6, top_k=3, low_memory=True, # Contrastive search
            repetition_penalty=1.0,
        )

    async def _complete(self, prompt:str)-> (str, int):
        result = ''
        output_tokens = 0
        try:
            
            loop = asyncio.get_running_loop()
            result = await loop.run_in_executor(None, self.pipe, prompt)
            result = result[0]['generated_text']
            output_tokens = len(self.model_tokenizer.tokenize(result))
            
        except Exception as e:
            result = ''
            self.logger.exception('Generation failed.')
        finally:
            torch.cuda.empty_cache()
            return result, output_tokens