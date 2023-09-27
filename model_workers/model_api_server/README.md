# Model API Server
A framework to serve and compose LLMs.

### Standalone Environment

1. Create virtual environment (recommend)
```bash
python -m venv .venv
source .venv/bin/activate
```

2. Install dependency
```bash
pip install -r requirements.txt
```

3. Install the model-api-server framework
```bash
pip install .

# or, you can specify the "editable" option to synchronize the local package
# with this directory

pip install --editable .

```

4. Run the example
- You need to run the Agent first
```bash
cd example
./run.sh
```

## Container Environment

1. Build the base image
```bash
docker build -t model-api .
```
2. Build the image of example worker
The filter of this example will convert Simplified Chinese to traditional Chinese with [OpenCC](https://github.com/BYVoid/OpenCC)
Moreover, the model is a simple reflect model, i.e. output the input.
The developer can easily extend this example. For more details, please refer to the "Development" section.
```bash
docker build -t model-api-chinese-convert example
```

### Build Base Image

### Build Example Image

### Run Example

## Development

### LLM

### Text-level Filter

### Layout