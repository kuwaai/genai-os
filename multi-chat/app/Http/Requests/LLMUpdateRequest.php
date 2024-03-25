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
            'access_code' => ['string', 'max:255', Rule::unique('llms')->ignore($id)],
            'order' => ['nullable','digits_between:-1000000,1000000'],
            'version' => ['nullable', 'max:255'],
            'description' => ['nullable', 'max:255'],
            "system_prompt"=>["nullable", "max:1024"],
            "react_btn"=>["nullable", "array"],
        ];
    }
}
