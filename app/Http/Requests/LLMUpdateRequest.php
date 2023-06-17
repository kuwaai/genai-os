<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LLMUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->input('id');
        return [
            'image' => ['image'],
            'id' => ['integer', 'digits_between:1,255', Rule::exists('llms', 'id')],
            'name' => ['string', 'max:255', Rule::unique('llms')->ignore($id)],
            'link' => ['string', 'max:1024'],
            'limit_per_day' => ['integer', 'digits_between:-1,1000000'],
            'order' => ['integer', 'digits_between:-1000000,1000000'],
            'access_code' => ['string', 'max:1024', Rule::unique('llms')->ignore($id)],
        ];
    }
}
