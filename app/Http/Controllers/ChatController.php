<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use App\Models\Histories;
use App\Jobs\RequestChat;
use App\Models\Chats;
use App\Models\LLMs;
use App\Models\User;

class ChatController extends Controller
{
    public function main(Request $request)
    {
        $chat = Chats::findOrFail($request->route("chat_id"));
        if ($chat->user_id != Auth::user()->id) {
            return redirect()->route('chat');
        } elseif (LLMs::findOrFail($chat->llm_id)->enabled == true) {
            return view('chat');
        }
        return redirect()->route('archives', $request->route("chat_id"));
    }

    public function create(ChatRequest $request): RedirectResponse
    {
        $chat = new Chats();
        $chat->fill(['name' => substr($request->input('input'), 0, 64), 'llm_id' => $request->input('llm_id'), 'user_id' => $request->user()->id]);
        $chat->save();
        $history = new Histories();
        $history->fill(['msg' => $request->input('input'), 'chat_id' => $chat->id, 'isbot' => false]);
        $history->save();
        $llm = LLMs::findOrFail($request->input('llm_id'));
        Redis::set($chat->id . 'status', 'pending');
        RequestChat::dispatch($chat->id, $request->input('input'), $llm->API);
        return Redirect::route('chats', $chat->id);
    }

    public function request(Request $request): RedirectResponse
    {
        $chatId = $request->input('chat_id');
        $history = new Histories();
        $history->fill(['msg' => $request->input('input'), 'chat_id' => $chatId, 'isbot' => false]);
        $history->save();
        $API = LLMs::findOrFail(Chats::findOrFail($chatId)->llm_id)->API;
        Redis::del($chatId);
        Redis::set($chatId . 'status', 'pending');
        RequestChat::dispatch($chatId, $request->input('input'), $API);
        return Redirect::route('chats', $chatId)->with('status', $request->input('req-failed'));
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
        } catch (ModelNotFoundException $e) {
            // Handle the exception here, for example:
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $chat->delete();
        return Redirect::route('chat');
    }

    public function edit(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
        } catch (ModelNotFoundException $e) {
            // Handle the exception here, for example:
            return response()->json(['error' => 'Chat not found'], 404);
        }
        $chat->fill(['name' => $request->input('new_name')]);
        $chat->save();
        return Redirect::route('chats', $request->input('id'));
    }

    public function ResetRedis(Request $request)
    {
        Redis::flushAll();
        return Redirect::route('dashboard');
    }

    public function SSE(Request $request)
    {
        set_time_limit(20);
        $chatId = $request->input('chat_id');
        $response = response()->stream(function () use ($chatId) {
            $extendedLimit = false;
            $sent = '';
            if ($chatId) {
                try {
                    if (Redis::get($chatId . 'status') && Redis::get($chatId . 'status') != 'finished') {
                        set_time_limit(20); # It should output something in 20 seconds
                        while ((Redis::get($chatId) && Redis::get($chatId) != $sent) || Redis::get($chatId . 'status') != 'finished') {
                            $result = Redis::get($chatId);
                            $encoding = mb_detect_encoding($result, 'UTF-8, ISO-8859-1', true);
                            if ($encoding !== 'UTF-8') {
                                $result = mb_convert_encoding($result, 'UTF-8', $encoding);
                            }
                            $newData = mb_substr($result, mb_strlen($sent, 'utf-8'), null, 'utf-8');
                            $sent = $result;
                            $length = mb_strlen($newData, 'utf-8');
                            for ($i = 0; $i < $length; $i++) {
                                $char = mb_substr($newData, $i, 1, 'utf-8');
                                if (mb_check_encoding($char, 'utf-8')) {
                                    echo 'data: ' . $char . "\n\n";
                                    set_time_limit(10); # each token shouldn't taken more than 10 seconds
                                    ob_flush();
                                    flush();
                                }
                            }
                        }
                        usleep(250000);
                    }
                } catch (\Throwable $e) {
                }
            }
            echo "event: close\n\n";
            ob_flush();
            flush();
            Redis::del($chatId);
            Redis::del($chatId . 'status');
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('charset', 'utf-8');
        return $response;
    }
}
