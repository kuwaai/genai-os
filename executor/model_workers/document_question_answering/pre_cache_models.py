#!/bin/python3

from langchain.embeddings import HuggingFaceEmbeddings

def main():
    models = [
        {
            'class': HuggingFaceEmbeddings,
            # 'name': 'paraphrase-multilingual-MiniLM-L12-v2' 
            'name': 'infgrad/stella-base-zh' 
        }
    ]

    for model in models:
        print('Pre-caching model: {}'.format(model['name']))
        model['class'](model_name=model['name'])

if __name__ == '__main__':
    main()