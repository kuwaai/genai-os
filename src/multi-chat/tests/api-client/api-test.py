import os
import logging
import argparse
import openai

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
    parser = argparse.ArgumentParser(
        description="Test the Kuwa API.",
        formatter_class=argparse.ArgumentDefaultsHelpFormatter
    )
    parser.add_argument("--base-url", default=os.environ.get('KUWA_API_BASE_URL', 'http://localhost/v1.0/'), help="The custom base URL for the Kuwa API.")
    parser.add_argument("--api-key", default=os.environ.get('KUWA_API_KEY'), help="The API token for authentication with Kuwa.")
    parser.add_argument("--model", default="gemini-pro", help="The custom base URL for the Kuwa API.")
    parser.add_argument("--log", type=str, default="INFO", help="the log level. (INFO, DEBUG, ...)")
    args = parser.parse_args()

    # Setup logger
    numeric_level = getattr(logging, args.log.upper(), None)
    if not isinstance(numeric_level, int):
        raise ValueError(f'Invalid log level: {args.log}')
    logging.basicConfig(level=numeric_level)
    
    api_client = TestKuwaApi(
        base_url = args.base_url,
        api_key = args.api_key,
        model = args.model
    )
    api_client.test_non_stream()
    api_client.test_stream()