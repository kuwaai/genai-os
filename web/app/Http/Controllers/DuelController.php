<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Jobs\RequestChat;
use App\Jobs\ImportChat;
use App\Models\DuelChat;
use App\Models\Histories;
use App\Models\Chats;
use App\Models\LLMs;
use GuzzleHttp\Client;
use DB;
use Session;

class DuelController extends Controller
{
    public function abort(Request $request)
    {
        $chatIDs = Chats::where('dcID', '=', $request->route('duel_id'))
            ->pluck('id')
            ->toArray();
        $list = Histories::whereIn('id', \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1))
            ->whereIn('chat_id', $chatIDs)
            ->pluck('id')
            ->toArray();
        $client = new Client(['timeout' => 300]);
        $agent_location = \App\Models\SystemSetting::where('key', 'agent_location')->first()->value;
        $response = $client->post($agent_location . RequestChat::$agent_version . '/chat/abort', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'history_id' => json_encode($list),
                'user_id' => Auth::user()->id,
            ],
        ]);
        return response('Aborted', 200);
    }
    public function main(Request $request)
    {
        $duel_id = $request->route('duel_id');
        if ($duel_id) {
            $chat = DuelChat::findOrFail($duel_id);
            if ($chat->user_id != Auth::user()->id) {
                return redirect()->route('duel.home');
            } elseif (true) {
                #LLMs::findOrFail($chat->llm_id)->enabled == true) {
                return view('duel');
            }
        }
        return view('duel');
        #return redirect()->route('archives', $request->route('chat_id'));
    }

    public function import(Request $request)
    {
        $historys = $request->input('history');
        $llm_ids = $request->input('llm_ids');
        if ($historys) {
            $result = DB::table(function ($query) {
                $query
                    ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                    ->from('group_permissions')
                    ->join('permissions', 'perm_id', '=', 'permissions.id')
                    ->where('group_id', Auth()->user()->group_id)
                    ->where('name', 'like', 'model_%')
                    ->get();
            }, 'tmp')
                ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
                ->select('llms.id')
                ->where('llms.enabled', true)
                ->get()
                ->pluck('id')
                ->toarray();
            $historys = json_decode($historys);
            if ($historys) {
                //JSON format
                $historys = $historys->messages;
            } else {
                //TSV Format
                $rows = explode("\n", str_replace("\r\n", "\n", $request->input('history')));
                $historys = [];
                $headers = null;

                foreach ($rows as $index => $row) {
                    // Splitting each row into columns using tabs as delimiter
                    if ($index === 0) {
                        $headers = explode("\t", $row);
                        if (in_array('content', $headers)) {
                            continue;
                        } else {
                            $headers = ['content'];
                        }
                    }
                    if ($headers === null) {
                        break;
                    }
                    $columns = explode("\t", $row);

                    $record = [];
                    foreach ($headers as $columnIndex => $header) {
                        if (!isset($columns[$columnIndex]) || empty($columns[$columnIndex])) {
                            continue;
                        }
                        $value = $columns[$columnIndex];
                        if ($header === 'content') {
                            $value = trim(json_decode('"' . $value . '"'), '"');
                        }
                        $record[$header] = $value;
                    }
                    $historys[] = (object) $record;
                }
            }
            if ($historys) {
                //Permission check
                $access_codes = [];
                foreach ($historys as $message) {
                    if (isset($message->role) && is_string($message->role)) {
                        $model = isset($message->model) && is_string($message->model) ? $message->model : null;
                        if ($message->role === 'assistant') {
                            if (!is_null($model) && !in_array($model, $access_codes)) {
                                $access_codes[] = $model;
                            }
                        }
                    }
                }
                if ($access_codes || $llm_ids) {
                    if ($access_codes) {
                        $llm_ids = LLMs::whereIn('access_code', $access_codes)
                            ->select('id', 'access_code')
                            ->get();
                    } else {
                        $llm_ids = LLMs::whereIn('id', $llm_ids)
                            ->select('id', 'access_code')
                            ->get();
                    }
                    $access_codes = [];
                    foreach ($llm_ids as $i) {
                        if (in_array($i->id, $result)) {
                            $access_codes[] = $i->access_code;
                        }
                    }
                    //Filtering
                    $chainValue = null;
                    $data = [];
                    $flag = false;
                    foreach ($historys as $message) {
                        if ((isset($message->role) && is_string($message->role)) || !isset($message->role)) {
                            if (((isset($message->role) && $message->role === 'user') || !isset($message->role)) && isset($message->content) && is_string($message->content) && trim($message->content) !== '') {
                                if ($flag) {
                                    $newMessage = (object) [
                                        'role' => 'assistant',
                                        'model' => '',
                                        'chain' => $chainValue,
                                        'content' => '',
                                    ];
                                    if ($chainValue === true) {
                                        $newMessage->chain = true;
                                    }
                                    foreach ($access_codes as $access_code) {
                                        $newMessage->model = $access_code;

                                        $data[] = clone $newMessage;
                                    }
                                }
                                $chainValue = isset($message->chain) ? (bool) $message->chain : false;
                                if (!isset($message->role)) {
                                    $message->role = 'user';
                                }
                                $data[] = $message;
                                $flag = true;
                            } elseif ($message->role === 'assistant') {
                                $model = isset($message->model) && is_string($message->model) ? $message->model : null;
                                $content = isset($message->content) && is_string($message->content) ? $message->content : '';
                                $message->content = $content;
                                $message->model = $model;
                                if ($chainValue === true) {
                                    $message->chain = true;
                                }
                                if (is_null($model)) {
                                    $flag = false;
                                    foreach ($access_codes as $access_code) {
                                        $newMessage = clone $message;
                                        $newMessage->model = $access_code;

                                        if ($chainValue === true) {
                                            $newMessage->chain = true;
                                        }
                                        $data[] = $newMessage;
                                    }
                                } elseif (in_array($model, $access_codes)) {
                                    $flag = false;
                                    $data[] = $message;
                                }
                            }
                        }
                    }
                    if ($flag) {
                        $newMessage = (object) [
                            'role' => 'assistant',
                            'model' => '',
                            'chain' => $chainValue,
                            'content' => '',
                        ];
                        if ($chainValue === true) {
                            $newMessage->chain = true;
                        }
                        foreach ($access_codes as $access_code) {
                            $newMessage->model = $access_code;

                            $data[] = clone $newMessage;
                        }
                    }
                    $historys = $data;
                    if (count($historys) > 0) {
                        //Start loading
                        $Duel = new DuelChat();
                        $Duel->fill(['name' => $historys[0]->content, 'user_id' => $request->user()->id]);
                        $Duel->save();
                        $deltaTime = count($historys);
                        foreach ($llm_ids->pluck('id') as $id) {
                            $chat = new Chats();
                            $chat->fill(['name' => 'Duel Chat', 'llm_id' => $id, 'user_id' => Auth::user()->id, 'dcID' => $Duel->id]);
                            $chat->save();
                            $chatIds[] = $chat->id;
                        }
                        $flag = true;
                        $user_msg = null;
                        $appended = [];
                        $ids = [];
                        $t = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $deltaTime . ' second'));
                        $chatIds = Chats::join('llms', 'llm_id', '=', 'llms.id')
                            ->whereIn('chats.id', $chatIds)
                            ->select('chats.id', 'llms.access_code')
                            ->get();
                        foreach ($historys as $history) {
                            $history->isbot = $history->role == 'user' ? false : true;
                            if ($history->isbot) {
                                if ($user_msg != null && !in_array($history->model, $appended)) {
                                    $record = new Histories();
                                    $record->fill(['msg' => $user_msg, 'chat_id' => $chatIds->where('access_code', '=', $history->model)->first()->id, 'isbot' => false, 'chained' => $history->chain, 'created_at' => $t, 'updated_at' => $t]);
                                    $record->save();
                                }
                                $appended[] = $history->model;
                                $t2 = date('Y-m-d H:i:s', strtotime($t . ' +' . array_count_values($appended)[$history->model] . ' second'));
                                $record = new Histories();
                                $record->fill(['msg' => $history->content == '' ? '* ...thinking... *' : $history->content, 'chat_id' => $chatIds->where('access_code', '=', $history->model)->first()->id, 'chained' => $history->chain, 'isbot' => true, 'created_at' => $t2, 'updated_at' => $t2]);
                                $record->save();
                                if ($history->content == '') {
                                    $ids[] = $record->id;
                                    Redis::rpush('usertask_' . $request->user()->id, $record->id);
                                }
                            } else {
                                $user_msg = $history->content;
                                $t = date('Y-m-d H:i:s', strtotime($t . ' +' . ($appended != [] ? max(array_count_values($appended)) : 1) + 1 . ' second'));
                                $appended = [];
                            }
                        }
                        ImportChat::dispatch($ids, Auth::user()->id);
                        return Redirect::route('duel.chat', $Duel->id);
                    }
                }
            }
        }
        return redirect()->route('duel.home');
    }

    public function create(Request $request): RedirectResponse
    {
        $llms = $request->input('llm');
        $selectedLLMs = $request->input('chatsTo');
        if (count($selectedLLMs) > 0 && count($llms) > 1) {
            $result = DB::table(function ($query) {
                $query
                    ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                    ->from('group_permissions')
                    ->join('permissions', 'perm_id', '=', 'permissions.id')
                    ->where('group_id', Auth()->user()->group_id)
                    ->where('name', 'like', 'model_%')
                    ->get();
            }, 'tmp')
                ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
                ->select('llms.id')
                ->where('llms.enabled', true)
                ->get()
                ->pluck('id')
                ->toarray();

            foreach ($llms as $i) {
                if (!in_array($i, $result)) {
                    return back();
                }
            }
            # model permission auth done
            foreach ($selectedLLMs as $id) {
                if (!in_array($id, $llms)) {
                    return back();
                }
            }
            $input = $request->input('input');
            $Duel = new DuelChat();
            $Duel->fill(['name' => $input, 'user_id' => Auth::user()->id]);
            $Duel->save();
            $ct = date('Y-m-d H:i:s');
            $dct = date('Y-m-d H:i:s', strtotime($ct . ' +1 second'));
            foreach ($llms as $llm) {
                $chat = new Chats();
                $chat->fill(['name' => 'Duel Chat', 'llm_id' => $llm, 'user_id' => Auth::user()->id, 'dcID' => $Duel->id]);
                $chat->save();
                if (in_array($llm, $selectedLLMs)) {
                    $history = new Histories();
                    $history->fill(['msg' => $input, 'chat_id' => $chat->id, 'isbot' => false, 'created_at' => $ct, 'updated_at' => $ct]);
                    $history->save();
                    $history = new Histories();
                    $history->fill(['msg' => '* ...thinking... *', 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => $dct, 'updated_at' => $dct]);
                    $history->save();
                    RequestChat::dispatch(json_encode([['msg' => $input, 'isbot' => false]]), LLMs::findOrFail($chat->llm_id)->access_code, Auth::user()->id, $history->id, Auth::user()->openai_token);
                    Redis::rpush('usertask_' . Auth::user()->id, $history->id);
                }
            }
        }
        return redirect()
            ->to(route('duel.chat', $Duel->id) . ($request->input('limit') ? '?limit=' . $request->input('limit') : ''))
            ->with('selLLMs', $selectedLLMs);
    }

    public function new(Request $request): RedirectResponse
    {
        $llms = $request->input('llm');
        if (
            request()
                ->user()
                ->hasPerm('Duel_update_new_chat') &&
            count($llms) > 1
        ) {
            $result = DB::table(function ($query) {
                $query
                    ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                    ->from('group_permissions')
                    ->join('permissions', 'perm_id', '=', 'permissions.id')
                    ->where('group_id', Auth()->user()->group_id)
                    ->where('name', 'like', 'model_%')
                    ->get();
            }, 'tmp')
                ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
                ->select('llms.id')
                ->where('llms.enabled', true)
                ->get()
                ->pluck('id')
                ->toarray();

            foreach ($llms as $i) {
                if (!in_array($i, $result)) {
                    return back();
                }
            }

            return redirect()
                ->route('duel.home')
                ->with('llms', $llms);
        }

        return redirect()->route('duel.home');
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $ids = [];
            $chats = DuelChat::findOrFail($request->input('id'));
            foreach (Chats::where('dcID', '=', $chats->id)->get() as $chat) {
                $ids[] = $chat->llm_id;
                Histories::where('chat_id', '=', $chat->id)->delete();
            }
            Chats::where('dcID', '=', $chats->id)->delete();
            $chats->delete();
        } catch (ModelNotFoundException $e) {
            Log::error('Chat not found: ' . $request->input('id'));
        }
        return redirect()
            ->to(route('duel.home') . ($request->input('limit') ? '?limit=' . $request->input('limit') : ''))
            ->with('llms', $ids);
    }

    public function edit(Request $request): RedirectResponse
    {
        try {
            $chat = DuelChat::findOrFail($request->input('id'));
            $chat->fill(['name' => $request->input('new_name')]);
            $chat->save();
        } catch (ModelNotFoundException $e) {
            Log::error('Chat not found: ' . $request->input('id'));
        }
        return redirect()->to(route('duel.chat', $request->input('id')) . ($request->input('limit') ? '?limit=' . $request->input('limit') : ''));
    }

    public function request(Request $request): RedirectResponse
    {
        $duelId = $request->input('duel_id');
        $selectedLLMs = $request->input('chatsTo');
        $input = $request->input('input');
        $chained = Session::get('chained') == true;
        if (count($selectedLLMs) > 0 && $duelId && $input) {
            $chats = Chats::where('dcID', $request->input('duel_id'))->get();
            if (
                Chats::join('llms', 'llms.id', '=', 'llm_id')
                    ->where('dcID', $request->input('duel_id'))
                    ->get()
                    ->where('enabled', false)
                    ->count() == 0
            ) {
                $result = DB::table(function ($query) {
                    $query
                        ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                        ->from('group_permissions')
                        ->join('permissions', 'perm_id', '=', 'permissions.id')
                        ->where('group_id', Auth()->user()->group_id)
                        ->where('name', 'like', 'model_%')
                        ->get();
                }, 'tmp')
                    ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
                    ->select('llms.id')
                    ->where('llms.enabled', true)
                    ->get()
                    ->pluck('id')
                    ->toarray();

                foreach ($chats->pluck('llm_id')->toarray() as $i) {
                    if (!in_array($i, $result)) {
                        return back();
                    }
                }
                foreach ($selectedLLMs as $id) {
                    if (!in_array($id, $chats->pluck('llm_id')->toarray())) {
                        return back();
                    }
                }
                #Model permission checked
                $start = date('Y-m-d H:i:s');
                $deltaStart = date('Y-m-d H:i:s', strtotime($start . ' +1 second'));
                foreach ($chats as $chat) {
                    if (in_array($chat->llm_id, $selectedLLMs)) {
                        $history = new Histories();
                        $history->fill(['msg' => $input, 'chat_id' => $chat->id, 'isbot' => false, 'created_at' => $start, 'updated_at' => $start]);
                        $history->save();
                        if (in_array(LLMs::find($chat->llm_id)->access_code, ['doc_qa', 'web_qa', 'doc_qa_b5', 'web_qa_b5']) && !$chained) {
                            $tmp = json_encode([
                                [
                                    'msg' => Histories::where('chat_id', '=', $chat->id)
                                        ->select('msg')
                                        ->orderby('created_at')
                                        ->orderby('id', 'desc')
                                        ->get()
                                        ->first()->msg,
                                    'isbot' => false,
                                ],
                                ['msg' => $request->input('input'), 'isbot' => false],
                            ]);
                        } elseif ($chained) {
                            $tmp = Histories::where('chat_id', '=', $chat->id)
                                ->select('msg', 'isbot')
                                ->orderby('created_at')
                                ->orderby('id', 'desc')
                                ->get()
                                ->toJson();
                        } else {
                            $tmp = json_encode([['msg' => $input, 'isbot' => false]]);
                        }

                        $history = new Histories();
                        $history->fill(['msg' => '* ...thinking... *', 'chained' => $chained, 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => $deltaStart, 'updated_at' => $deltaStart]);
                        $history->save();
                        RequestChat::dispatch($tmp, LLMs::findOrFail($chat->llm_id)->access_code, Auth::user()->id, $history->id, Auth::user()->openai_token);
                        Redis::rpush('usertask_' . Auth::user()->id, $history->id);
                    }
                }
            }
        }
        return redirect()
            ->to(route('duel.chat', $duelId) . ($request->input('limit') ? '?limit=' . $request->input('limit') : ''))
            ->with('selLLMs', $selectedLLMs);
    }
}
