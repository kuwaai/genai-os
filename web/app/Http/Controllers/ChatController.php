<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $chat = Chats::findOrFail($request->route('chat_id'));
        if ($chat->user_id != Auth::user()->id) {
            return redirect()->route('chat');
        } elseif (LLMs::findOrFail($chat->llm_id)->enabled == true) {
            return view('chat');
        }
        return redirect()->route('archives', $request->route('chat_id'));
    }

    public function create(ChatRequest $request): RedirectResponse
    {
        $input = $request->input('input');
        if ($input) {
            $chat = new Chats();
            $chat->fill(['name' => $input, 'llm_id' => $request->input('llm_id'), 'user_id' => $request->user()->id]);
            $chat->save();
            $history = new Histories();
            $history->fill(['msg' => $input, 'chat_id' => $chat->id, 'isbot' => false]);
            $history->save();
            $history = new Histories();
            $history->fill(['msg' => '* ...thinking... *', 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'))]);
            $history->save();
            $llm = LLMs::findOrFail($request->input('llm_id'));
            Redis::rpush('usertask_' . Auth::user()->id, $history->id);
            RequestChat::dispatch(
                $chat->id,
                $input,
                $llm->access_code,
                Auth::user()->id,
                $history->id,
                Auth::user()->openai_token,
            );
        }
        return Redirect::route('chats', $chat->id);
    }

    public function request(Request $request): RedirectResponse
    {
        $chatId = $request->input('chat_id');
        $input = $request->input('input');
        if ($chatId && $input) {
            $history = new Histories();
            $history->fill(['msg' => $input, 'chat_id' => $chatId, 'isbot' => false]);
            $history->save();
            $history = new Histories();
            $history->fill(['msg' => '* ...thinking... *', 'chat_id' => $chatId, 'isbot' => true, 'created_at' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'))]);
            $history->save();
            $access_code = LLMs::findOrFail(Chats::findOrFail($chatId)->llm_id)->access_code;
            Redis::rpush('usertask_' . Auth::user()->id, $history->id);
            RequestChat::dispatch(
                $chatId,
                $input,
                $access_code,
                Auth::user()->id,
                $history->id,
                Auth::user()->openai_token,
            );
        }
        return Redirect::route('chats', $chatId);
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = Chats::findOrFail($request->input('id'));
        } catch (ModelNotFoundException $e) {
            // Handle the exception here, for example:
            return Redirect::route('chat');
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
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('charset', 'utf-8');
        $response->headers->set('Connection', 'close');

        $response->setCallback(function () use ($response) {
            global $listening;
            $listening = Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
            if (count($listening) > 0) {
                $client = Redis::connection();
                $client->subscribe($listening, function ($message, $raw_history_id) use ($client, $response) {
                    global $listening;
                    [$type, $msg] = explode(' ', $message, 2);
                    $history_id = substr($raw_history_id, strrpos($raw_history_id, '_') + 1);
                    if ($type == 'Ended') {
                        $key = array_search($history_id, $listening);
                        if ($key !== false) {
                            unset($listening[$key]);
                        }
                        if (count($listening) == 0) {
                            echo "event: close\n\n";
                            ob_flush();
                            flush();
                            $client->disconnect();
                        }
                    } elseif ($type == 'New') {
                        echo 'data: ' . $history_id . ',' . str_replace("\n", '[NEWLINEPLACEHOLDERUWU]', $msg) . "\n\n";
                        ob_flush();
                        flush();
                    }
                });
            }
        });

        return $response;
    }
}