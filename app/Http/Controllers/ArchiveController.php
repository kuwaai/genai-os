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
            return redirect()->route('archive');
        } elseif (LLMs::findOrFail($chat->llm_id)->enabled == false) {
            return view('archive');
        }
        return redirect()->route('chats', $request->route('chat_id'));
    }
    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $chat->delete();
        return Redirect::route('archive');
    }

    public function edit(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Chat not found'], 404);
        }
        $chat->fill(['name' => $request->input('new_name')]);
        $chat->save();
        return Redirect::route('archives', $request->input('id'));
    }
}
