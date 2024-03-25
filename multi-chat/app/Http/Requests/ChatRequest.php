<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'llm_id' => ['integer', Rule::exists('llms', 'id')],
            'input' => ['string']
        ];
    }
}
