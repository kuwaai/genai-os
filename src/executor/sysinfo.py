import os
import sys
import asyncio
import logging
import json
import subprocess
import platform
from importlib.metadata import version
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

import kuwa.executor
from kuwa.executor import LLMExecutor, Modelfile
logger = logging.getLogger(__name__)


class SysInfoExecutor(LLMExecutor):
    """
    Print the system info
    """
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        pass

    def setup(self):
        self.stop = False

    def get_kuwa_info(self):
        result  = [
            "**System information**",
            f"- Platform: {platform.platform()}",
            f"- Kuwa-executor version: {version('kuwa-executor')}"
        ]
        return result
    
    def get_torch_info(self):
        try:
            import torch
        except ImportError:
            return ["- Torch is not installed."]
        result = [
            "**Torch information**",
            f"- Pytorch version: {torch.__version__}",
            f"- Is CUDA available?: {torch.cuda.is_available()}",
            ]
        if torch.cuda.is_available():
            device = torch.device('cuda')
            result += [
                f"- Linked CUDA version: {torch.version.cuda}",
                f"- Number of CUDA devices: {torch.cuda.device_count()}",
                f"- A torch tensor: {torch.rand(5).to(device)}",
            ]
        return result

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):

        sysinfo = self.get_kuwa_info() + [''] + self.get_torch_info()
        yield '\n'.join(sysinfo)

    async def abort(self):
        return "Aborted"

if __name__ == "__main__":
    executor = SysInfoExecutor()
    executor.run()