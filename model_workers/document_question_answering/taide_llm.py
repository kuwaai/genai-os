#!/usr/bin/env python
# coding: utf-8

import torch, accelerate, time
import chevron
import logging
from pathlib import Path
from transformers import AutoModelForCausalLM, AutoConfig, AutoTokenizer, StoppingCriteria, StoppingCriteriaList, pipeline

from model_api_server.datatype import ChatRecord, Role

class StopOnTokens(StoppingCriteria):
    def __init__(self, tokenizer: AutoTokenizer):
        stop_list = ['<s>', '</s>', '[INST]', '\nQuestion:', "[INST: ]"]
        to_token_id = lambda x: torch.LongTensor(tokenizer(x)['input_ids']).to('cuda')
        self.stop_token_ids = [to_token_id(x) for x in stop_list]
    
    def __call__(self, input_ids: torch.LongTensor, scores: torch.FloatTensor, **kwargs) -> bool:
        for stop_ids in self.stop_token_ids:
            if torch.all(input_ids[0][-len(stop_ids):] == stop_ids):
                return True
        return False

class TaideLlm:
    def __init__(self,
                 token_limit = 3500,
                 model_path = '/llm/llama2-7b-chat-b1.0.0',
                 prompt_template_path = 'prompt_template/taide.mustache'):
        self.logger = logging.getLogger(__name__)
        
        self.token_limit = token_limit

        model = AutoModelForCausalLM.from_pretrained(
            model_path,
            config=AutoConfig.from_pretrained(model_path),
            device_map="auto",
            torch_dtype=torch.float16
        )
        model.eval()
        tokenizer = AutoTokenizer.from_pretrained(model_path)

        self.pipe = pipeline(
            model=model,
            tokenizer=tokenizer,
            return_full_text=True,
            task='text-generation',
            stopping_criteria=StoppingCriteriaList([StopOnTokens(tokenizer)]),
            temperature=0.2,
            max_new_tokens=2048,
            repetition_penalty = 1.0,
            do_sample=True
        )

        prompt_template_file = Path(prompt_template_path)
        self.prompt_template = prompt_template_file.read_text()
        self.logger.info('Prompt template: {}'.format(self.prompt_template))

    async def complete(self, chat_history: [ChatRecord], system_prompt: str): 
        result = ''
        try:
            data = []
            for chat in chat_history:
                if chat.role == Role.USER:
                    data.append({'user': chat.msg})
                elif chat.role == Role.BOT:
                    data[-1]['bot'] = chat.msg
            
            self.logger.info('Data: {}'.format(data))
            
            prompt = ''
            
            # Trim the over-length history
            while True:
                data[0]['system'] = system_prompt
                prompt = chevron.render(self.prompt_template, {'history': data})
                self.logger.info('Prompt: {}'.format(prompt))
                if len(prompt) < self.token_limit: break
                data = data[1:]
            
            self.logger.info('Prompt: {}'.format(prompt))
            
            self.logger.info('Generating...')
            result = self.pipe(prompt)[0]['generated_text']
            torch.cuda.empty_cache()
            
        except Exception as e:
            result = ''
            print(e)
        finally:
            torch.cuda.empty_cache()
            self.logger.info('Generation finished.')
            return result
