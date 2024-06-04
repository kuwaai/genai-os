<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Jobs\RequestChat;
use App\Models\Histories;
use App\Jobs\ImportChat;
use App\Models\ChatRoom;
use App\Models\Chats;
use GuzzleHttp\Client;
use App\Models\LLMs;
use App\Models\Bots;
use DB;
use Session;

class RoomController extends Controller
{
    public function share(Request $request)
    {
        $chat = ChatRoom::find($request->route('room_id'));
        if ($chat && $chat->user_id == Auth::user()->id) {
            return view('room.share');
        } else {
            return redirect()->route('room.home');
        }
    }
    public function abort(Request $request)
    {
        $chatIDs = Chats::where('roomID', '=', $request->route('room_id'))->pluck('id')->toArray();
        $list = Histories::whereIn('id', \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1))
            ->whereIn('chat_id', $chatIDs)
            ->pluck('id')
            ->toArray();
        $client = new Client(['timeout' => 300]);
        $agent_location = \App\Models\SystemSetting::where('key', 'agent_location')->first()->value;
        $response = $client->post($agent_location . '/' . RequestChat::$agent_version . '/chat/abort', [
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
        $room_id = $request->route('room_id');
        if ($room_id) {
            $chat = ChatRoom::find($room_id);
            if ($chat == null || $chat->user_id != Auth::user()->id) {
                return redirect()->route('room.home');
            } else {
                #LLMs::findOrFail($chat->bot_id)->enabled == true) {
                return view('room');
            }
        }
        return view('room');
        #return redirect()->route('archives', $request->route('chat_id'));
    }

    public function import(Request $request)
    {
        $historys = $request->input('history');
        $bot_ids = $request->input('llm_ids');
        $room_id = $request->input('room_id');
        $filename = $request->input('import_file_name');
        if ($historys) {
            $result = Bots::pluck('id')->toarray();
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
                        $headers = explode("\t", str_replace('    ', "\t", $row));
                        if (in_array('content', $headers)) {
                            continue;
                        } else {
                            $headers = ['content'];
                        }
                    }
                    if ($headers === null) {
                        break;
                    }
                    if (count($headers) === 1) {
                        $columns = [str_replace('    ', "\t", $row)];
                    } else {
                        $columns = explode("\t", str_replace('    ', "\t", $row));
                    }

                    $record = [];
                    foreach ($headers as $columnIndex => $header) {
                        if (!isset($columns[$columnIndex]) || empty($columns[$columnIndex])) {
                            continue;
                        }
                        $value = $columns[$columnIndex];
                        if ($header === 'content') {
                            $value = trim(json_decode('"' . $value . '"'), '"');
                            if ($value === '') {
                                $value = str_replace("\\n", "\n", str_replace("\\t", "\t", $columns[$columnIndex]));
                            }
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
                if ($access_codes || $bot_ids || $room_id) {
                    if ($bot_ids) {
                        $bot_ids = Bots::whereIn('bots.id', $bot_ids)->join('llms', 'bots.model_id', '=', 'llms.id')->select('bots.id', 'access_code')->get();
                    } else {
                        $bot_ids = collect([]);
                    }

                    if ($access_codes) {
                        $bot_ids = $bot_ids->merge(LLMs::whereIn('access_code', $access_codes)->join('bots', 'bots.model_id', '=', 'llms.id')->where('visibility', '=', 0)->select('bots.id', 'access_code')->get())->unique('id');
                    }

                    $access_codes = [];
                    foreach ($bot_ids as $i) {
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
                                $chainValue = isset($message->chain) ? (bool) $message->chain : (Session::get('chained') ?? true) == true;
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
                        if ($room_id) {
                            $Room = ChatRoom::findorfail($room_id);
                            $chats = Chats::where('roomID', '=', $room_id);
                            $chatIds = $chats->pluck('id');
                            foreach ($bot_ids->pluck('id')->diff($chats->pluck('bot_id')) as $id) {
                                $chat = new Chats();
                                $chat->fill(['name' => 'Room Chat', 'bot_id' => $id, 'user_id' => Auth::user()->id, 'roomID' => $Room->id]);
                                $chat->save();
                                $chatIds[] = $chat->id;
                            }
                        } else {
                            $Room = new ChatRoom();
                            $Room->fill(['name' => $filename ?? $historys[0]->content, 'user_id' => $request->user()->id]);
                            $Room->save();
                            foreach ($bot_ids->pluck('id') as $id) {
                                $chat = new Chats();
                                $chat->fill(['name' => 'Room Chat', 'bot_id' => $id, 'user_id' => Auth::user()->id, 'roomID' => $Room->id]);
                                $chat->save();
                                $chatIds[] = $chat->id;
                            }
                        }
                        $flag = true;
                        $user_msg = null;
                        $appended = [];
                        $ids = [];
                        $deltaTime = count($historys);
                        $t = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $deltaTime . ' second'));
                        $chatIds = Chats::join('bots', 'bots.id', '=', 'chats.bot_id')->join('llms', 'bots.model_id', '=', 'llms.id')->whereIn('chats.id', $chatIds)->select('chats.id', 'llms.access_code')->get();
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
                                    Redis::expire('usertask_' . $request->user()->id, 1200);
                                }
                            } else {
                                $user_msg = $history->content;
                                $t = date('Y-m-d H:i:s', strtotime($t . ' +' . ($appended != [] ? max(array_count_values($appended)) : 1) + 1 . ' second'));
                                $appended = [];
                            }
                        }
                        ImportChat::dispatch($ids, Auth::user()->id);
                        return Redirect::route('room.chat', $Room->id)->with('selLLMs', $bot_ids->pluck('id')->toarray());
                    }
                }
            }
        }
        return redirect()->route('room.home');
    }

    public function upload_file(Request $request)
    {
        if ($request->file()) {
            $request->validate([
                'file' => 'max:20480',
            ]);
            $directory = 'pdfs/' . $request->user()->id; // Directory relative to 'public/storage/'
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
            $url = url('storage/' . $directory . '/' . rawurlencode($fileName));
            return $url;
        }
        return null;
    }
    function getWebPageTitle($url)
    {
        // Try to fetch the HTML content of the URL
        $html = @file_get_contents($url);

        // If the URL is not accessible, return an empty string
        if ($html === false) {
            return '';
        }

        // Use regular expressions to extract the title from the HTML
        if (preg_match('/<title>(.*?)<\/title>/i', $html, $matches)) {
            return $matches[1];
        } else {
            // If no title is found, return an empty string
            return '';
        }
    }
    function getFilenameFromURL($url)
    {
        $path_parts = pathinfo($url);

        if (isset($path_parts['filename'])) {
            return $path_parts['filename'];
        } else {
            return '';
        }
    }

    public function create(Request $request): RedirectResponse
    {
        $llms = $request->input('llm');
        $selectedLLMs = $request->input('chatsTo');
        if (count($selectedLLMs) > 0 && count($llms) > 0) {
            $result = Bots::wherein(
                'model_id',
                DB::table('group_permissions')
                    ->join('permissions', 'group_permissions.perm_id', '=', 'permissions.id')
                    ->select(DB::raw('substring(permissions.name, 7) as model_id'), 'perm_id')
                    ->where('group_permissions.group_id', Auth::user()->group_id)
                    ->where('permissions.name', 'like', 'model_%')
                    ->get()
                    ->pluck('model_id'),
            )
                ->pluck('bots.id')
                ->toarray();

            foreach ($llms as $i) {
                if (!in_array($i, $result)) {
                    return Redirect::route('room.home');
                }
            }
            # model permission auth done
            foreach ($selectedLLMs as $id) {
                if (!in_array($id, $llms)) {
                    return Redirect::route('room.home');
                }
            }
            $input = $request->input('input');
            $next_input = '';
            $url = $this->upload_file($request);
            if ($url) {
                $next_input = $input;
                $input = $url;
            }
            $chatname = $input;
            $first_url = preg_match('/\bhttps?:\/\/\S+/i', $input, $matches);
            $firstUrl = isset($matches[0]) ? $matches[0] : null;
            if ($firstUrl) {
                $tmp = $this->getWebPageTitle($firstUrl);
                if ($tmp != '') {
                    $chatname = rawurldecode($tmp);
                } else {
                    $tmp = $this->getFilenameFromURL($firstUrl);
                    if ($tmp != '') {
                        $chatname = rawurldecode($tmp);
                    }
                }
            }

            $Room = new ChatRoom();
            $Room->fill(['name' => $chatname, 'user_id' => Auth::user()->id]);
            $Room->save();
            $ct = date('Y-m-d H:i:s');
            $dct = date('Y-m-d H:i:s', strtotime($ct . ' +1 second'));
            foreach ($llms as $llm) {
                $chat = new Chats();
                $chat->fill(['name' => 'Room Chat', 'bot_id' => $llm, 'user_id' => Auth::user()->id, 'roomID' => $Room->id]);
                $chat->save();
                if (in_array($llm, $selectedLLMs)) {
                    $history = new Histories();
                    $history->fill(['msg' => $input, 'chat_id' => $chat->id, 'isbot' => false, 'created_at' => $ct, 'updated_at' => $ct]);
                    $history->save();
                    $history = new Histories();
                    $history->fill(['msg' => '* ...thinking... *', 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => $dct, 'updated_at' => $dct]);
                    $history->save();
                    RequestChat::dispatch(json_encode([['msg' => $input, 'isbot' => false]]), LLMs::findOrFail(Bots::findOrFail($chat->bot_id)->model_id)->access_code, Auth::user()->id, $history->id, App::getLocale(), null, json_decode(Bots::find($llm)->config ?? '')->modelfile ?? null);
                    Redis::rpush('usertask_' . Auth::user()->id, $history->id);
                    Redis::expire('usertask_' . Auth::user()->id, 1200);
                }
            }
        }
        return redirect()
            ->route('room.chat', $Room->id)
            ->with('selLLMs', $selectedLLMs)
            ->with('mode_track', request()->input('mode_track'))
            ->with('next_input', $next_input);
    }

    public function new(Request $request): RedirectResponse
    {
        $llms = $request->input('llm');
        if (request()->user()->hasPerm('Room_update_new_chat') && count($llms) > 0) {
            $result = Bots::wherein(
                'model_id',
                DB::table('group_permissions')
                    ->join('permissions', 'group_permissions.perm_id', '=', 'permissions.id')
                    ->select(DB::raw('substring(permissions.name, 7) as model_id'), 'perm_id')
                    ->where('group_permissions.group_id', Auth::user()->group_id)
                    ->where('permissions.name', 'like', 'model_%')
                    ->get()
                    ->pluck('model_id'),
            )
                ->pluck('bots.id')
                ->toarray();

            foreach ($llms as $i) {
                if (!in_array($i, $result)) {
                    return Redirect::route('room.home');
                }
            }

            return redirect()->route('room.home')->with('llms', $llms);
        }

        return redirect()->route('room.home');
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $ids = [];
            $chats = ChatRoom::findOrFail($request->input('id'));
            foreach (Chats::where('roomID', '=', $chats->id)->get() as $chat) {
                $ids[] = $chat->bot_id;
                Histories::where('chat_id', '=', $chat->id)->delete();
            }
            Chats::where('roomID', '=', $chats->id)->delete();
            $chats->delete();
        } catch (ModelNotFoundException $e) {
            Log::error('Chat not found: ' . $request->input('id'));
        }
        return redirect()->route('room.home')->with('llms', $ids);
    }

    public function edit(Request $request): RedirectResponse
    {
        try {
            $chat = ChatRoom::findOrFail($request->input('id'));
            $chat->fill(['name' => $request->input('new_name')]);
            $chat->save();
        } catch (ModelNotFoundException $e) {
            Log::error('Chat not found: ' . $request->input('id'));
        }
        return redirect()->route('room.chat', $request->input('id'));
    }

    public function request(Request $request): RedirectResponse
    {
        $roomId = $request->input('room_id');
        $selectedLLMs = $request->input('chatsTo');
        $input = $request->input('input');
        $url = $this->upload_file($request);
        $next_input = '';
        if ($url) {
            $next_input = $input;
            $input = $url;
        }

        $chained = (Session::get('chained') ?? true) == true;
        if (count($selectedLLMs) > 0 && $roomId && $input) {
            $chats = Chats::where('roomID', $request->input('room_id'))->get();
            $result = Bots::pluck('id')->toarray();

            foreach ($chats->pluck('bot_id')->toarray() as $i) {
                if (!in_array($i, $result)) {
                    return Redirect::route('room.home');
                }
            }
            foreach ($selectedLLMs as $id) {
                if (!in_array($id, $chats->pluck('bot_id')->toarray())) {
                    return Redirect::route('room.home');
                }
            }
            #Model permission checked
            $start = date('Y-m-d H:i:s');
            $deltaStart = date('Y-m-d H:i:s', strtotime($start . ' +1 second'));
            foreach ($chats as $chat) {
                if (in_array($chat->bot_id, $selectedLLMs)) {
                    $history = new Histories();
                    $history->fill(['msg' => $input, 'chat_id' => $chat->id, 'isbot' => false, 'created_at' => $start, 'updated_at' => $start]);
                    $history->save();
                    $access_code = LLMs::findOrFail(Bots::findOrFail($chat->bot_id)->model_id)->access_code;
                    if (in_array($access_code, ['doc_qa', 'web_qa', 'doc_qa_b5', 'web_qa_b5']) && !$chained) {
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
                        $tmp = json_encode([['msg' => $request->input('input'), 'isbot' => false]]);
                    }

                    $history = new Histories();
                    $history->fill(['msg' => '* ...thinking... *', 'chained' => $chained, 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => $deltaStart, 'updated_at' => $deltaStart]);
                    $history->save();
                    RequestChat::dispatch($tmp, $access_code, Auth::user()->id, $history->id, App::getLocale(), null, json_decode(Bots::find($chat->bot_id)->config ?? '')->modelfile ?? null);
                    Redis::rpush('usertask_' . Auth::user()->id, $history->id);
                    Redis::expire('usertask_' . Auth::user()->id, 1200);
                }
            }
        }
        return redirect()->route('room.chat', $roomId)->with('selLLMs', $selectedLLMs)->with('mode_track', request()->input('mode_track'))->with('next_input', $next_input);
    }
}
