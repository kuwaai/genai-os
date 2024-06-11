import os
import gc
import io
import sys
import asyncio
import logging
import json
import base64
import torch
from diffusers import StableDiffusionPipeline, EulerDiscreteScheduler
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile

logger = logging.getLogger(__name__)

def image_to_data_url(img):
        buffered = io.BytesIO()
        img.save(buffered, format="JPEG")
        return 'data:image/jpeg;base64,' + base64.b64encode(buffered.getvalue()).decode("utf-8")

class StableDiffusionExecutor(LLMExecutor):
    model_name:str = "stabilityai/stable-diffusion-2"

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model', type=str, default=self.model_name, help='The name of the stable diffusion model to use.')

    def setup(self):
        self.model_name = self.args.model
        self.stop = False

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        model_name = modelfile.parameters.get("model_name", self.model_name)
        logger.debug(f"Model name: {model_name}")
        yield "<<<WARNING>>>Loading model...<<</WARNING>>>"
        scheduler = EulerDiscreteScheduler.from_pretrained(model_name, subfolder="scheduler")
        pipe = StableDiffusionPipeline.from_pretrained(model_name, scheduler=scheduler, torch_dtype=torch.float16)
        pipe = pipe.to("cuda")
        yield "<<<WARNING>>>Model loaded. Generating...<<</WARNING>>>"

        prompt = next(i for i in reversed(history) if i["role"] == "user")["content"]
        logger.debug(f"Prompt: {prompt}")
        image = pipe(prompt).images[0]

        yield "![{}]({})".format(prompt, image_to_data_url(image))

        del pipe, scheduler
        gc.collect()
        torch.cuda.empty_cache()

    async def abort(self):
        self.stop = True
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = StableDiffusionExecutor()
    executor.run()