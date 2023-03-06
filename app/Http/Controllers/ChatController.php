<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use App\Models\Histories;
use App\Models\Chats;
use App\Models\LLMs;
use App\Models\User;

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
        $llm = LLMs::findOrFail($request->input('llm_id'));

        #return Redirect::route('chats', $chat->id);
        $token = $request->user()->tokens()->where('name', 'API_Token')->where('abilities', 'like', '%access_api%')->first()->token;
        return Redirect::route('chats', $chat->id)->with('msg', $request->input('input'))->with('token', $token)->with('api', $llm->API)->with("chat_id",$chat->id);
    }

    public function request(Request $request): RedirectResponse
    {
        $history = new Histories();
        $history->fill(['msg' => $request->input('input'), 'chat_id' => $request->input('chat_id'), 'isbot' => false]);
        $history->save();
        $API = LLMs::findOrFail(Chats::findOrFail($request->input('chat_id'))->llm_id)->API;
        $token = $request->user()->tokens()->where('name', 'API_Token')->where('abilities', 'like', '%access_api%')->first()->token;
        return Redirect::route('chats', $request->input('chat_id'))->with('msg', $request->input('input'))->with('token', $token)->with('api', $API)->with("chat_id",$request->input('chat_id'));
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
            $chat->delete();
        } catch (ModelNotFoundException $e) {
            // Handle the exception here, for example:
            return response()->json(['error' => 'Chat not found'], 404);
        }

        return Redirect::route('chat');
    }

    public function edit(Request $request): RedirectResponse
    {
        $chat = Chats::findOrFail($request->input('id'));
        $chat->fill(['name' => $request->input('new_name')]);
        $chat->save();
        return Redirect::route('chats', $request->input('id'));
    }
}
