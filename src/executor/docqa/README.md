Doc QA
---
A Retrieval-Augmented Generation Executor.

## Production Deployment

This executor has two configurations.
1. DocQA/WebQA: Retrieve information from the documents or web pages
2. DatabaseQA(DBQA): Retrieve information from a pre-built vector database
3. SearchQA: Retrieve information from a search engine

### Prerequisite
1. Install the dependency
```sh
cd src/executor/webqa
pip install -r requirements.txt
```
2. You can obtain the KUWA_TOKEN from the website's profile > API Token Management > Kuwa Chat API Token

### DocQA/WebQA
```
python ./docqa.py --access_code doc-qa --api_base_url http://localhost/ --api_key <KUWA_TOKEN> [--model <MODEL_NAME>]
```

### DBQA
```
python ./docqa.py --access_code db-qa --api_base_url http://localhost/ --api_key <KUWA_TOKEN> [--model <MODEL_NAME>] --database /path/to/pre-built/vector-db
```

### SearchQA
```
python ./searchqa.py --access_code search-qa --api_base_url http://localhost/ --api_key <KUWA_TOKEN> [--model <MODEL_NAME>] --google_api_key <GOOGLE_SEARCH_API_KEY> --google_cse_id <GOOGLE_CUSTOM_SEARCH_ENDING_ID> [--advanced_search_params <ADVANCED_SEARCH_PARAMETERS>]
```