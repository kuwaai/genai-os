import os
import gc
import io
import sys
import asyncio
import logging
import json
import base64
import torch
from enum import Enum
from functools import lru_cache
from diffusers import (
    AutoPipelineForText2Image,
    AutoPipelineForImage2Image,
    AutoPipelineForInpainting
)
from diffusers.utils import load_image
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.llm_executor import extract_last_url

logger = logging.getLogger(__name__)

def image_to_data_url(img):
        buffered = io.BytesIO()
        img.save(buffered, format="JPEG")
        return 'data:image/jpeg;base64,' + base64.b64encode(buffered.getvalue()).decode("utf-8")

class Task(Enum):
    TEXT2IMG = 1
    IMG2IMG = 2
    INPAINTING = 3

class StableDiffusionExecutor(LLMExecutor):
    model_name:str = "stabilityai/stable-diffusion-2"
    n_cache:int = 3

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model', type=str, default=self.model_name, help='The name of the stable diffusion model to use.')
        model_group.add_argument('--n_cache', type=int, default=self.n_cache, help='How many models to cache.')
        
        display_group = parser.add_argument_group('Display Options')
        model_group.add_argument('--show_progress', action="store_true", help='Whether to show the progress of generation.')

    def setup(self):
        self.model_name = self.args.model
        self.n_cache = self.args.n_cache
        self.show_progress = self.args.show_progress
        self.stop = False
        setattr(self, "load_pipe", lru_cache(maxsize=self.n_cache)(self._load_pipe))

    def _load_pipe(self, task:Task, model_name:str):
        pipe_class_map = {
            Task.TEXT2IMG: AutoPipelineForText2Image,
            Task.IMG2IMG: AutoPipelineForImage2Image,
            Task.INPAINTING: AutoPipelineForInpainting
        }
        pipe_class = pipe_class_map[task]
        pipe = pipe_class.from_pretrained(model_name, torch_dtype=torch.float16)
        return pipe

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        model_name = modelfile.parameters.get("model_name", self.model_name)
        generation_conf = modelfile.parameters["imgen_"] # num_inference_steps=2, strength=0.5, guidance_scale=0.0
        img_url, history = extract_last_url(history)
        prompt = next(i for i in reversed(history) if i["role"] == "user")["content"]
        logger.debug(f"Model name: {model_name}")
        logger.debug(f"Prompt: {prompt}")
        logger.debug(f"Initial image: {img_url}")
        if not prompt:
            yield "Please enter prompt"
            return

        if self.show_progress: yield "<<<WARNING>>>[PROGRESS]Generating...<<</WARNING>>>"

        pipe = None
        if img_url is not None:
            pipe = self.load_pipe(task=Task.IMG2IMG, model_name=model_name)
            init_image = load_image(img_url).resize((512, 512))
            generation_conf["image"] = init_image
        else:
            pipe = self.load_pipe(task=Task.TEXT2IMG, model_name=model_name)

        assert pipe is not None
        pipe = pipe.to("cuda")
        result_image = pipe(prompt, **generation_conf).images[0]

        yield "![{}]({})".format(prompt, image_to_data_url(result_image))

        gc.collect()
        torch.cuda.empty_cache()

        logger.info("Done")

    async def abort(self):
        self.stop = True
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = StableDiffusionExecutor()
    executor.run()