Doc QA
---
A Retrieval-Augmented Generation Executor.

## Production Deployment

This executor has two configurations.
1. DocQA/WebQA: Retrieve information from the documents or web pages
2. DatabaseQA(DBQA): Retrieve information from a pre-built vector database

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
python ./docqa.py --access_code dbqa --api_base_url http://localhost/ --api_key <KUWA_TOKEN> [--model <MODEL_NAME>] --database /path/to/pre-built/vector-db
```