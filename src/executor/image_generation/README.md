# Kuwa Painter

The Kuwa Painter can generate an image from a text input.

## Known Issues and Limitations
### Hardware Requirements

The default model uses stable-diffusion-2. The VRAM consumption when running on a GPU is shown in the table below.

| Model Name | VRAM Requirement |
| ------ | ------- |
| stable-diffusion-2 | ~3GB |
| stable-diffusion-xl-base-1.0 | ~8GB |
| sdxl-turbo | ~8GB |
| stable-diffusion-3-medium-diffusers | ~18 GB |

### Known Limitations

- sdxl-turbo will error when doing img2img

## How to Use

1. Refer to the startup methods of the Executors for each version of Kuwa to start the Executor for the Kuwa Painter.
    1. For the Windows version, please refer to the directory `windows/executors/painter`.
    2. For the Docker version, please refer to the configuration file `docker/compose/painter.yaml`.

2. An Executor named Painter should be added to your Kuwa system. You can enter a text string to generate an image.
    - Example Prompt: "A cinematic shot of a baby racoon wearing an intricate italian priest robe."

3. You can adjust the model used by referring to the [Configuration Guide](#Configuration-Guide).

## Configuration Guide

The Kuwa Painter can adjust the generation parameters through the Modelfile in the frontend Store. Commonly used adjustable parameters are as follows:

```dockerfile
PARAMETER model_name stabilityai/stable-diffusion-xl-base-1.0
PARAMETER imgen_num_inference_steps 40 # The number of denoising steps. More denoising steps usually lead to a higher quality image at the expense of slower inference
PARAMETER imgen_negative_prompt "" #The prompt or prompts to guide what to not include in image generation. If not defined, you need to pass negative_prompt_embeds instead. Ignored when not using guidance (guidance_scale < 1).
PARAMETER imgen_strength 0.5 #Indicates extent to transform the reference image. Must be between 0 and 1. image is used as a starting point and more noise is added the higher the strength. The number of denoising steps depends on the amount of noise initially added. When strength is 1, added noise is maximum and the denoising process runs for the full number of iterations specified in num_inference_steps. A value of 1 essentially ignores image.
PARAMETER imgen_guidance_scale 0.0 #A higher guidance scale value encourages the model to generate images closely linked to the text prompt at the expense of lower image quality. Guidance scale is enabled when guidance_scale 
PARAMETER imgen_denoising_end 0.8 # What % of steps to be run on each experts (80/20) (SDXL only)
```

In addition, the Kuwa Painter can also be configured via dynamic command line parameters. The configurable parameters are as follows:

```
Model Options:
  --model MODEL         The name of the stable diffusion model to use. (default: stabilityai/stable-diffusion-2)
  --n_cache N_CACHE     How many models to cache. (default: 3)

Display Options:
  --show_progress       Whether to show the progress of generation. (default: False)
```