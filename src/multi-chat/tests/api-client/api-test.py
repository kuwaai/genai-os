import os
import openai
import logging

logger = logging.getLogger(__name__)

class TestKuwaApi:

    def __init__(self, base_url, api_key, model):
        logger.info(base_url)
        logger.info(api_key)
        self.client = openai.OpenAI(
            base_url = base_url,
            api_key = api_key
        )
        self.model = model

    def test_non_stream(self):
        response = self.client.chat.completions.create(
            model=self.model,
            messages=[
                {'role': 'user', 'content': 'Introduce yourself.'}
            ],
            temperature=0,
        )
        logger.info(response)
        print("****************")
    
    def test_stream(self):
        response = self.client.chat.completions.create(
            model=self.model,
            messages=[
                {'role': 'user', 'content': 'Introduce yourself.'}
            ],
            temperature=0,
            stream=True 
        )

        for chunk in response:
            logger.info(chunk)
            logger.info(chunk.choices[0].delta.content)
            print("****************")

if __name__ == '__main__':
    logging.basicConfig(level=logging.INFO)
    api_client = TestKuwaApi(
        base_url = os.environ.get('KUWA_API_BASE_URL'),
        api_key = os.environ.get('KUWA_API_KEY'),
        model = 'gemini-pro'
    )
    api_client.test_non_stream()
    api_client.test_stream()