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
            'link' => ['string', 'max:1024', Rule::unique('llms')],
            'limit_per_day' => ['integer', 'digits_between:-1,1000000'],
            'order' => ['integer', 'digits_between:-1000000,1000000'],
            'API' => ['string', 'max:1024', Rule::unique('llms')],
        ];
    }
}
