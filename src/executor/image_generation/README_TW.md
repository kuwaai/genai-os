# Kuwa Painter

Kuwa Painter 可以輸入一段文字產生圖片，或是上傳一張圖片並搭上一段文字產生圖片。

## 已知問題與限制
### 硬體需求

預設模型使用 stable-diffusion-2，若跑在GPU上所消耗 VRAM 如下表所示。

|模型名稱|VRAM需求|
|------|-------|
| stable-diffusion-2            | ~3GB |
| stable-diffusion-xl-base-1.0  | ~8GB |
| sdxl-turbo                    | ~8GB |
| stable-diffusion-3-medium-diffusers | ~18 GB |

### 已知限制
- sdxl-turbo進行img2img時會出錯


## 使用方法

1. 參考各版本 Kuwa 的 Executor 啟動方式，啟動 Kuwa Painter 的 Executor
    1. Windows 版請參考目錄 `windows/executors/painter`
    2. Docker 版請參考設定檔 `docker/compose/painter.yaml`

2. 一個名為 Painter 的 Executor 應會被加入您的 Kuwa 系統中，您可以輸入一段文字來產生圖片，或是上傳一張參考圖片圖片並搭上一段文字產生圖片
    - 範例 Prompt: `A cinematic shot of a baby racoon wearing an intricate italian priest robe.`
3. 可以參考[設定簡介章節](#設定簡介)調整使用模型，

## 設定簡介

Kuwa Painter 可以透過前端 Store 中的 Modelfile 調整生成參數，常用可調整參數如下
```dockerfile
PARAMETER model_name stabilityai/stable-diffusion-xl-base-1.0
PARAMETER imgen_num_inference_steps 40 # The number of denoising steps. More denoising steps usually lead to a higher quality image at the expense of slower inference
PARAMETER imgen_negative_prompt "" #The prompt or prompts to guide what to not include in image generation. If not defined, you need to pass negative_prompt_embeds instead. Ignored when not using guidance (guidance_scale < 1).
PARAMETER imgen_strength 0.5 #Indicates extent to transform the reference image. Must be between 0 and 1. image is used as a starting point and more noise is added the higher the strength. The number of denoising steps depends on the amount of noise initially added. When strength is 1, added noise is maximum and the denoising process runs for the full number of iterations specified in num_inference_steps. A value of 1 essentially ignores image.
PARAMETER imgen_guidance_scale 0.0 #A higher guidance scale value encourages the model to generate images closely linked to the text prompt at the expense of lower image quality. Guidance scale is enabled when guidance_scale 
PARAMETER imgen_denoising_end 0.8 # What % of steps to be run on each experts (80/20) (SDXL only)
```

此外，Kuwa Painter 也可以透過動命令列參數設定，可設定參數如下

```
Model Options:
  --model MODEL         The name of the stable diffusion model to use. (default: stabilityai/stable-diffusion-2)
  --n_cache N_CACHE     How many models to cache. (default: 3)

Display Options:
  --show_progress       Whether to show the progress of generation. (default: False)
```