<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use App\Models\Histories;
use App\Jobs\RequestChat;
use App\Jobs\ImportChat;
use GuzzleHttp\Client;
use App\Models\Chats;
use App\Models\LLMs;
use App\Models\User;
use App\Models\Feedback;
use App\Models\APIHistories;
use App\Models\Groups;
use DB;
use Session;

class ChatController extends Controller
{
    public function translate(Request $request)
    {
        $record = Histories::find($request->route('history_id'));

        if (!$record) {
            return response('Unauthorized', 401);
        }

        $chat = Chats::find($record->chat_id);

        if (!$chat || $chat->user_id !== Auth::user()->id) {
            return response('Unauthorized', 401);
        }

        $access_code = $request->input('model');
        $msg = $record->msg;
        if ($access_code == null && strpos(Groups::find($request->user()->group_id)->describe, '!verilog_translate!') === 0){
            $access_code = LLMs::find($chat->bot_id)->access_code;
            $msg = "請將程式碼轉成verilog。\n" . $msg;
        }
        else if ($access_code == null) {
            $access_code = LLMs::find($chat->bot_id)->access_code;
            $msg = "以下提供內容，請幫我翻譯成中文。\n" . $msg;
        }

        $tmp = json_encode([['isbot' => false, 'msg' => $msg]]);
        if ($access_code == 'safety-guard') {
            if ($record->chained) {
                $tmp = Histories::where('chat_id', '=', $record->chat_id)
                    ->where('id', '<=', $record->id)
                    ->where('created_at', '<=', $record->created_at)
                    ->select('msg', 'isbot')
                    ->orderby('created_at')
                    ->orderby('id', 'desc')
                    ->get()
                    ->toJson();
            } else {
                $tmp = json_encode([
                    [
                        'isbot' => false,
                        'msg' => Histories::where('chat_id', '=', $record->chat_id)
                            ->where('isbot', '=', false)
                            ->orderby('created_at')
                            ->orderby('id', 'desc')
                            ->get()
                            ->last()->msg,
                    ],
                    ['isbot' => true, 'msg' => $msg],
                ]);
            }
        }
        $history = new APIHistories();
        $history->fill(['input' => $tmp, 'output' => '* ...thinking... *', 'user_id' => Auth::user()->id]);
        $history->save();
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('charset', 'utf-8');
        $response->headers->set('Connection', 'close');

        $response->setCallback(function () use ($history, $tmp, $access_code) {
            $client = new Client(['timeout' => 300]);
            Redis::rpush('api_' . Auth::user()->id, $history->id);
            RequestChat::dispatch($tmp, $access_code, Auth::user()->id, $history->id, 'api_' . $history->id);

            $req = $client->get(route('api.stream'), [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'query' => [
                    'key' => config('app.API_Key'),
                    'user_id' => Auth::user()->id,
                    'history_id' => $history->id,
                ],
                'stream' => true,
            ]);
            $stream = $req->getBody();
            $result = '';
            $line = '';
            while (!$stream->eof()) {
                $char = $stream->read(1);

                if ($char === "\n") {
                    $line = trim($line);
                    if (substr($line, 0, 5) === 'data:') {
                        $jsonData = (object) json_decode(trim(substr($line, 5)));
                        if ($jsonData !== null) {
                            $tmp = mb_substr($jsonData->msg, mb_strlen($jsonData->msg, 'UTF-8') - 1, 1, 'UTF-8');
                            $result .= $tmp;
                            echo $tmp;
                            ob_flush();
                            flush();
                        }
                    } elseif (substr($line, 0, 6) === 'event:') {
                        if (trim(substr($line, 5)) == 'end') {
                            $client->disconnect();
                            break;
                        }
                    }
                    $line = '';
                } else {
                    $line .= $char;
                }
            }
            $history->fill(['output' => $result]);
            $history->save();
        });
        return $response;
    }

    public function update_chain(Request $request)
    {
        $state = ($request->input('switch') ?? true) == 'true';
        Session::put('chained', $state);
    }
    public function upload(Request $request)
    {
        if (count(Redis::lrange('usertask_' . Auth::user()->id, 0, -1)) == 0) {
            $result = DB::table(function ($query) {
                $query
                    ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                    ->from('group_permissions')
                    ->join('permissions', 'perm_id', '=', 'permissions.id')
                    ->where('group_id', Auth()->user()->group_id)
                    ->where('name', 'like', 'model_%')
                    ->get();
            }, 'tmp')
                ->join('llms', DB::raw('CAST(llms.id AS '. (config('database.default') == "mysql" ? 'CHAR' : 'TEXT') .')'), '=', 'tmp.model_id')
                ->select('llms.id')
                ->where('llms.enabled', true)
                ->get()
                ->pluck('id')
                ->toarray();
            $llm_id = $request->input('llm_id');

            $request->validate([
                'file' => 'required|max:10240',
            ]);
            if ($llm_id && in_array($llm_id, $result) && $request->file()) {
                // Start uploading the file
                $user = $request->user();
                $userId = $user->id;
                $directory = 'pdfs/' . $userId; // Directory relative to 'public/storage/'
                $storagePath = public_path('storage/' . $directory); // Adjusted path

                $fileName = time() . '_' . $request->file->getClientOriginalName();
                $filePath = $request->file('file')->storeAs($directory, $fileName, 'public'); // Use 'public' disk

                $files = File::files($storagePath);

                if (count($files) > 5) {
                    usort($files, function ($a, $b) {
                        return filectime($a) - filectime($b);
                    });

                    while (count($files) > 5) {
                        $oldestFile = array_shift($files);
                        File::delete($storagePath . '/' . $oldestFile->getFilename());
                    }
                }
                //Create a chat and send that url into the llm
                $msg = url('storage/' . $directory . '/' . rawurlencode($fileName));
                $chat = new Chats();

                $chatname = explode('_', $fileName)[1];
                $chat->fill(['name' => $chatname, 'llm_id' => $llm_id, 'user_id' => $request->user()->id]);
                $chat->save();
                $history = new Histories();
                $history->fill(['msg' => $msg, 'chat_id' => $chat->id, 'isbot' => false]);
                $history->save();
                $history = new Histories();
                $history->fill(['msg' => '* ...thinking... *', 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'))]);
                $history->save();
                Redis::rpush('usertask_' . Auth::user()->id, $history->id);
                RequestChat::dispatch(json_encode([['msg' => $msg, 'isbot' => false]]), LLMs::find($llm_id)->access_code, Auth::user()->id, $history->id);
                return Redirect::route('chat.chat', $chat->id);
            }
        }
        return Redirect::route('chat.new', $request->input('llm_id'));
    }

    public function feedback(Request $request)
    {
        $history_id = $request->input('history_id');
        if ($history_id) {
            $tmp = Histories::find($history_id);
            if ($tmp) {
                $tmp = Chats::find($tmp->chat_id)->roomID;
                if (($tmp != null && Auth::user()->hasPerm('Room_update_feedback')) || ($tmp == null && Auth::user()->hasPerm('Chat_update_feedback'))) {
                    $nice = $request->input('type') == '1';
                    $detail = $request->input('feedbacks');
                    $flag = $request->input('feedback');
                    $init = $request->input('init');
                    $feedback = new Feedback();
                    if (Feedback::where('history_id', '=', $history_id)->exists()) {
                        $feedback = Feedback::where('history_id', '=', $history_id)->first();
                    }
                    if ($init) {
                        $feedback->fill(['history_id' => $history_id, 'nice' => $nice, 'detail' => null, 'flags' => null]);
                    } else {
                        $feedback->fill(['history_id' => $history_id, 'nice' => $nice, 'detail' => $detail, 'flags' => $flag == null ? null : json_encode($flag)]);
                    }
                    $feedback->save();
                }
            }
        }
        return response()->noContent();
    }

    public function SSE(Request $request)
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('charset', 'utf-8');
        $response->headers->set('Connection', 'close');
        set_exception_handler(function ($exception) {
            if ($exception->getMessage() != "Connection closed"){
                Log::error('Uncaught SSE Exception: ' . $exception->getMessage());
            }
        });
        $response->setCallback(function () use ($response, $request) {
            $channel = $request->input('channel');
            if ($channel != null && strpos($channel, 'aielection_') === 0) {
                $client = Redis::connection();
                $client->subscribe($channel, function ($message, $raw_history_id) use ($client, $response) {
                    [$type, $msg] = explode(' ', $message, 2);
                    if ($type == 'Ended') {
                        echo "event: close\n\n";
                        ob_flush();
                        flush();
                        $client->disconnect();
                    } elseif ($type == 'New') {
                        echo 'data: ' . json_encode(['msg' => json_decode($msg)->msg]) . "\n\n";
                        ob_flush();
                        flush();
                    }
                });
            } else {
                global $listening;
                $listening = Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                if (count($listening) > 0) {
                    foreach ($listening as $i) {
                        $history = Histories::find($i);
                        if (!$history) {
                            unset($listening[array_search($i, $listening)]);
                        } elseif ($history && $history->msg !== '* ...thinking... *') {
                            echo 'data: ' . json_encode(['history_id' => $i, 'msg' => $history->msg]) . "\n\n";
                            ob_flush();
                            flush();
                            unset($listening[array_search($i, $listening)]);
                        }
                    }
                    if (count($listening) == 0) {
                        echo "data: finished\n\n";
                        echo "event: close\n\n";
                        ob_flush();
                        flush();
                    }

                    $client = Redis::connection();
                    try {
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
                                    echo "data: finished\n\n";
                                    echo "event: close\n\n";
                                    ob_flush();
                                    flush();
                                    $client->disconnect();
                                }
                            } elseif ($type == 'New') {
                                echo 'data: ' . json_encode(['history_id' => $history_id, 'msg' => json_decode($msg)->msg]) . "\n\n";
                                ob_flush();
                                flush();
                            }
                        });
                    } catch (RedisException) {
                    }
                } else {
                    echo "data: finished\n\n";
                    echo "event: close\n\n";
                    ob_flush();
                    flush();
                }
            }
        });

        return $response;
    }

    public function compile_verilog(Request $request)
    {
        $verilogCode = $request->input('verilog_code');
        $result = true;
        try {
            $response = Http::post('http://127.0.0.1:13579/compile-verilog', [
                'verilog_code' => $verilogCode,
            ]);
        } catch (\Throwable) {
            $result = false;
        }
        if ($result && $response->successful()) {
            return $response;
        } else {
            return response()->json(['error' => $result ? "Compile failed" : 'Backend compiler offline'], 200);
        }
    }
}
