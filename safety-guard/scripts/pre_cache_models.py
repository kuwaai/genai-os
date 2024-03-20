#!/bin/python3

import os
from transformers import AutoModelForCausalLM
from ckip_transformers.nlp import CkipWordSegmenter

class HfWrapper:
  def __init__(self, model_name):
    token = os.environ.get('HF_TOKEN')
    AutoModelForCausalLM.from_pretrained(model_name, token=token)

def main():
    models = [
        {
            'class': HfWrapper,
            'name': 'meta-llama/LlamaGuard-7b'
        },
        {
            'class': CkipWordSegmenter,
            'name': 'albert-tiny'
        }
    ]

    for model in models:
        print('Pre-caching model: {}'.format(model['name']))
        model['class'](model['name'])

if __name__ == '__main__':
    main()