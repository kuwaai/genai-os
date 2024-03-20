<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\LLMs;


class LLMCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => ['image'],
            'name' => ['string', 'max:255', Rule::unique('llms')],
            'access_code' => ['string', 'max:255', Rule::unique('llms')],
            'order' => ['nullable','digits_between:-1000000,1000000'],
            'version' => ['nullable', 'max:255'],
            'link' => ['nullable', 'max:1024'],
            'description' => ['nullable', 'max:255'],
        ];
    }
}
