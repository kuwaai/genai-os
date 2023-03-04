<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChatRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Models\LLMs;
use App\Models\Chats;
use App\Models\Histories;

class ChatController extends Controller
{
    public function create(ChatRequest $request): RedirectResponse
    {
        $chat = new Chats();
        $chat->fill(['name' => $request->input('input'), 'llm_id' => $request->input('llm_id'), 'user_id' => $request->user()->id]);
        $chat->save();
        $history = new Histories();
        $history->fill(['msg' => $request->input('input'), 'chat_id' => $chat->id, 'isbot' => false]);
        $history->save();

        //WIP Send request to API server
        //And then get data back to the page

        return Redirect::route('chats', $chat->id);
    }

    public function view(Request $request): RedirectResponse
    {
        $chat = new Chats();
        $chat->fill(['name' => $request->input('input'), 'llm_id' => $request->input('llm_id'), 'user_id' => $request->user()->id]);
        $chat->save();

        return Redirect::route('view_chat');
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
            $chat->delete();
        } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the exception here, for example:
            return response()->json(['error' => 'Chat not found'], 404);
        }

        return Redirect::route('chat');
    }
}
