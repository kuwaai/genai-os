<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LLMUpdateRequest;
use App\Http\Requests\LLMCreateRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Models\LLMs;
use Illuminate\Support\Facades\Storage;

class LLMController extends Controller
{
    public function update(LLMUpdateRequest $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->input("id"));
        $validated = $request->validated();
        if ($file = $request->file('image')) {
            Storage::delete($model->image);
            $validated['image'] = $file->store('public/images');
        }
        $model->fill($validated);
        $model->save();
        return Redirect::route('dashboard');
    }

    public function delete(Request $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->input("id")); 
        Storage::delete($model->image);
        $model->delete();

        return Redirect::route('dashboard');
    }

    public function create(LLMCreateRequest $request): RedirectResponse
    {
        $model = new LLMs;
        $validated = $request->validated();
        if ($file = $request->file('image')) {
            $validated['image'] = $file->store('public/images');
        }
        $model->fill($validated);
        $model->save();
        return Redirect::route('dashboard');
    }
}
