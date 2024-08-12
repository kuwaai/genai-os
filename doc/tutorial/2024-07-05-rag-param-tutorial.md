Kuwa's RAG application (DocQA/WebQA/DatabaseQA/SearchQA) supports customization of advanced parameters through the Bot's model file starting from version v0.3.1, allowing a single Executor to be virtualized into multiple RAG applications. Detailed parameter descriptions and examples are as follows.

## Parameter Description
The following parameter contents are the default values for the v0.3.1 RAG application.

### Shared Parameters for All RAGs
```
PARAMETER retriever_embedding_model "thenlper/gte-base-zh" # Embedding model name
PARAMETER retriever_mmr_fetch_k 12 # MMR fetch k chunks
PARAMETER retriever_mmr_k 6 # MMR fetch k chunks
PARAMETER retriever_chunk_size 512 # Length of each chunk in characters (not restricted for DatabaseQA)
PARAMETER retriever_chunk_overlap 128 # Overlap length between chunks in characters (not restricted for DatabaseQA)
PARAMETER generator_model None # Specify which model to answer, None means auto-selection
PARAMETER generator_limit 3072 # Length limit of the entire prompt in characters
PARAMETER display_hide_ref False # Do not show references
```

### DocQA, WebQA, SearchQA Specific Parameters
```dockerfile
PARAMETER crawler_user_agent "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36" # Crawler UA string
```

### SearchQA Specific Parameters
```dockerfile
PARAMETER search_advanced_params "" # Advanced search parameters (SearchQA only)
PARAMETER search_num_url 3 # Number of search results to retrieve [1~10] (SearchQA only)
```

### DatabaseQA Specific Parameters
```dockerfile
PARAMETER retriever_database None # Path to vector database on local Executor
```

## Usage Example
Suppose you want to create a DatabaseQA knowledge base and specify a model to answer, you can create a Bot,  
select DocQA as the base model, and fill in the following Modelfile.

```dockerfile
PARAMETER generator_model "model_access_code" # Specify which model to answer, None means auto-selection
PARAMETER generator_limit 3072 # Length limit of the entire prompt in characters
PARAMETER retriever_database "/path/to/local/database/on/executor" # Path to vector database on local Executor
```