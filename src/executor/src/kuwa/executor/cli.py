import sys, os
import argparse
import importlib
sys.path.append(os.path.join(os.path.dirname(__file__), "./example"))
sys.path.append(os.path.join(os.path.dirname(__file__), "../../../"))

WORKERS = [
    {
        "name": "debug",
        "description": "[Tool] Debugging worker. It will reflect the last input.",
        "class": "debug.DebugWorker"
    },
    {
        "name": "dummy",
        "description": "[Tool] Dummy worker. It will reply fixed message regardless of the user prompt.",
        "class": "dummy.DummyWorker"
    },
    {
        "name": "geminipro",
        "description": "[Cloud model] Google Gemini-Pro. Need API key.",
        "class": "geminipro.GeminiWorker"
    },
    {
        "name": "chatgpt",
        "description": "[Cloud model] OpenAI ChatGPT. Need API key.",
        "class": "chatgpt.ChatGptWorker"
    },
    {
        "name": "huggingface",
        "description": "[On-premises model] Download and run Huggingface model locally.",
        "class": "huggingface.HuggingfaceWorker"
    },
    {
        "name": "llamacpp",
        "description": "[On-premises model] Run the GGUF model locally.",
        "class": "llamacpp.LlamaCppWorker"
    },
]

def import_class(name: str):
    """
    Import class from specified module
    """

    module_name, class_name = name.rsplit('.', 1)
    return getattr(importlib.import_module(module_name), class_name)

class ListAction(argparse.Action):
    def __init__(self, option_strings, dest, **kwargs):
        return super().__init__(option_strings, dest, nargs=0, default=argparse.SUPPRESS, **kwargs)
    
    def __call__(self, parser, namespace, values, option_string, **kwargs):
        name_width = max([len(i['name']) for i in WORKERS])
        print("Available model workers:\n")
        for worker in WORKERS:
            print(f"{worker['name']: <{name_width}}: {worker['description']}")
        print(f'\nUse "{os.path.basename(sys.argv[0])} [worker] --help" to get more information.\n')
        parser.exit()

class WorkerAction(argparse.Action):
    def __init__(self, option_strings, dest, **kwargs):
        return super().__init__(option_strings, dest, nargs=1, default=argparse.SUPPRESS, **kwargs)
    
    def __call__(self, parser, namespace, values, option_string, **kwargs):
        worker_name = values[0]
        idx = sys.argv.index(worker_name)
        sys.argv = [f"{sys.argv[0]} {worker_name}", *sys.argv[idx+1:]]

        worker_info = [i for i in WORKERS if i['name']==worker_name]
        assert len(worker_info) == 1
        worker_info = worker_info[0]

        worker_class = import_class(worker_info['class'])
        worker = worker_class()
        worker.run()
        parser.exit()

def main():
    
    parser = argparse.ArgumentParser()
    parser.add_argument('--list', action=ListAction, help='List the available workers.')
    parser.add_argument('worker', action=WorkerAction, choices=[i['name'] for i in WORKERS], help=f'Worker to invoke. Use "{sys.argv[0]} [worker] --help" to get more information')
    args, unknown_args = parser.parse_known_args( )

if __name__ == "__main__":
    main()