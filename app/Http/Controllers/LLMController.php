<?php

namespace App\Http\Controllers;

use App\Http\Requests\LLMUpdateRequest;
use App\Http\Requests\LLMCreateRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\LLMs;

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
        if (is_null($validated['order']))  unset($validated['order']);
        if (is_null($validated['version']))  unset($validated['version']);
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
        if (is_null($validated['order']))  unset($validated['order']);
        if (is_null($validated['version']))  unset($validated['version']);
        $model->fill($validated);
        $model->save();
        return Redirect::route('dashboard');
    }

    public function toggle(Request $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->route('llm_id'));
        $model->enabled = !$model->enabled;
        $model->save();
        return Redirect::route('dashboard');
    }
}
