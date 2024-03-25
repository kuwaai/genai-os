<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Chats;
use App\Models\LLMs;

class ArchiveController extends Controller
{
    public function main(Request $request)
    {
        $chat = Chats::findOrFail($request->route('chat_id'));
        if ($chat->user_id != Auth::user()->id) {
            return redirect()->route('archive.home');
        } elseif (LLMs::findOrFail($chat->llm_id)->enabled == false) {
            return view('archive');
        }
        return redirect()->route('chat.chat', $request->route('chat_id'));
    }
    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
            $chat->delete();
        } catch (ModelNotFoundException $e) {
            Log::error("Chat not found: " . $request->input('id'));
        }

        return Redirect::route('archive.home');
    }

    public function edit(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
            $chat->fill(['name' => $request->input('new_name')]);
            $chat->save();
        } catch (ModelNotFoundException $e) {
            Log::error("Chat not found: " . $request->input('id'));
        }
        return Redirect::route('archive.chat', $request->input('id'));
    }
}
