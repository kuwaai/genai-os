<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\LLMs;

class LLMUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->input('id');
        return [
            'id' => ['integer', 'digits_between:1,255', Rule::exists('llms', 'id')],
            'name' => ['string', 'max:255', Rule::unique('llms')->ignore($id)],
            'link' => ['string', 'max:1024', Rule::unique('llms')->ignore($id)],
            'limit_per_day' => ['integer', 'digits_between:-1,1000000'],
            'API' => ['string', 'max:1024', Rule::unique('llms')->ignore($id)],
        ];
    }
}
