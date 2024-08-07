<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class BotCreateRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'visibility' => $this->input('visibility', 1),
        ]);
    }
    public function rules(): array
    {
        return [
            'llm_access_code' => ['string', 'nullable'],
            'llm_name' => ['string'],
            'modelfile' => ['string', 'nullable'],
            'react_btn' => ['nullable'],
            'bot_name' => ['string'],
            'bot_describe' => ['string', 'nullable'],
            'visibility' => ['integer','digits_between:0,3']
        ];
    }
}
