<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LLMUpdateRequest;
use App\Http\Requests\LLMCreateRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Models\LLMs;

class LLMController extends Controller
{
    public function update(LLMUpdateRequest $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->input("id"));
        $model->fill($request->validated());
        $model->save();
        return Redirect::route('dashboard');
    }

    public function delete(Request $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->input("id")); 
        $model->delete();

        return Redirect::route('dashboard');
    }

    public function create(LLMCreateRequest $request): RedirectResponse
    {
        $model = new LLMs;
        $model->fill($request->validated());
        $model->save();
        return Redirect::route('dashboard');
    }
}
