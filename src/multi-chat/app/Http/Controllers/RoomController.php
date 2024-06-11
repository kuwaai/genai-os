<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Request;
use App\Jobs\RequestChat;
use App\Models\Histories;
use App\Jobs\ImportChat;
use App\Models\ChatRoom;
use App\Models\Chats;
use GuzzleHttp\Client;
use App\Models\LLMs;
use App\Models\Bots;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Arr;
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

    public function export_to_odt(Request $request)
    {
        $chat = ChatRoom::find($request->route('room_id'));
        if ($chat && $chat->user_id == Auth::user()->id) {
            $result = Bots::Join('llms', function ($join) {
                $join->on('llms.id', '=', 'bots.model_id');
            })
                ->where('llms.enabled', '=', true)
                ->wherein(
                    'bots.model_id',
                    DB::table('group_permissions')
                        ->join('permissions', 'group_permissions.perm_id', '=', 'permissions.id')
                        ->select(DB::raw('substring(permissions.name, 7) as model_id'), 'perm_id')
                        ->where('group_permissions.group_id', Auth::user()->group_id)
                        ->where('permissions.name', 'like', 'model_%')
                        ->get()
                        ->pluck('model_id'),
                )
                ->select('llms.*', 'bots.*', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'), 'llms.name as llm_name')
                ->orderby('llms.order')
                ->orderby('bots.created_at')
                ->get();
            $DC = ChatRoom::leftJoin('chats', 'chatrooms.id', '=', 'chats.roomID')
                ->where('chats.user_id', Auth::user()->id)
                ->select('chatrooms.*', DB::raw('count(chats.id) as counts'))
                ->groupBy('chatrooms.id');
            // Fetch the ordered identifiers based on `bot_id` for each database
            $DC = $DC->selectSub(function ($query) {
                if (config('database.default') == 'sqlite') {
                    $query->from('chats')->selectRaw("group_concat(bot_id, ',') as identifier")->whereColumn('roomID', 'chatrooms.id')->orderByRaw('bot_id');
                } elseif (config('database.default') == 'mysql') {
                    $query->from('chats')->selectRaw('group_concat(bot_id separator \',\' order by bot_id) as identifier')->whereColumn('roomID', 'chatrooms.id');
                } elseif (config('database.default') == 'pgsql') {
                    $query->from('chats')->selectRaw('string_agg(bot_id::text, \',\' order by bot_id) as identifier')->whereColumn('roomID', 'chatrooms.id');
                }
            }, 'identifier');

            // Get the final result and group by the ordered identifiers
            $DC = $DC->get()->groupBy('identifier');

            try {
                if (!session('llms')) {
                    $identifier = collect(Arr::flatten($DC->toarray(), 1))->where('id', '=', request()->route('room_id'))->first()['identifier'];
                    $DC = $DC[$identifier];
                    $llms = Bots::whereIn('bots.id', array_map('intval', explode(',', $identifier)))
                        ->join('llms', function ($join) {
                            $join->on('llms.id', '=', 'bots.model_id');
                        })
                        ->select('llms.*', 'bots.*', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'))
                        ->orderby('bots.id')
                        ->get();
                } else {
                    $llms = Bots::whereIn('bots.id', session('llms'))
                        ->Join('llms', function ($join) {
                            $join->on('llms.id', '=', 'bots.model_id');
                        })
                        ->select('llms.*', 'bots.*', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'))
                        ->orderby('bots.id')
                        ->get();
                    $DC = $DC[implode(',', $llms->pluck('id')->toArray())];
                }
            } catch (Exception $e) {
                $llms = Bots::whereIn('bots.id', session('llms'))
                    ->Join('llms', function ($join) {
                        $join->on('llms.id', '=', 'bots.model_id');
                    })
                    ->select('llms.*', 'bots.*', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'))
                    ->orderby('bots.id')
                    ->get();
                $DC = null;
            }
            $roomId = request()->route('room_id');

            $roomId = \Illuminate\Support\Facades\Request::route('room_id');

            $botChats = Chats::join('histories', 'chats.id', '=', 'histories.chat_id')
                ->leftJoin('feedback', 'history_id', '=', 'histories.id')
                ->join('bots', 'bots.id', '=', 'chats.bot_id')
                ->Join('llms', function ($join) {
                    $join->on('llms.id', '=', 'bots.model_id');
                })
                ->where('isbot', true)
                ->whereIn('chats.id', Chats::where('roomID', $roomId)->pluck('id'))
                ->select('histories.chained as chained', 'chats.id as chat_id', 'histories.id as id', 'chats.bot_id as bot_id', 'histories.created_at as created_at', 'histories.msg as msg', 'histories.isbot as isbot', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'), DB::raw('COALESCE(bots.name, llms.name) as name'), 'feedback.nice', 'feedback.detail', 'feedback.flags', 'access_code');

            $nonBotChats = Chats::join('histories', 'chats.id', '=', 'histories.chat_id')
                ->leftjoin('bots', 'bots.id', '=', 'chats.bot_id')
                ->Join('llms', function ($join) {
                    $join->on('llms.id', '=', 'bots.model_id');
                })
                ->where('isbot', false)
                ->whereIn('chats.id', Chats::where('roomID', $roomId)->pluck('id'))
                ->select('histories.chained as chained', 'chats.id as chat_id', 'histories.id as id', 'chats.bot_id as bot_id', 'histories.created_at as created_at', 'histories.msg as msg', 'histories.isbot as isbot', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'), DB::raw('COALESCE(bots.name, llms.name) as name'), DB::raw('NULL as nice'), DB::raw('NULL as detail'), DB::raw('NULL as flags'), 'access_code');

            $mergedChats = $botChats
                ->union($nonBotChats)
                ->get()
                ->sortBy(function ($chat) {
                    return [$chat->created_at, $chat->id, $chat->bot_id, -$chat->history_id];
                });
            $mergedMessages = [];
            // Filter and merge the chats based on the condition
            $filteredChats = $mergedChats->filter(function ($chat) use (&$mergedMessages) {
                if (!$chat->isbot && !in_array($chat->msg, $mergedMessages)) {
                    // Add the message to the merged messages array
                    $mergedMessages[] = $chat->msg;
                    return true; // Keep this chat in the final result
                } elseif ($chat->isbot) {
                    $mergedMessages = [];
                    return true; // Keep bot chats in the final result
                }
                return false; // Exclude duplicate non-bot chats
            });

            // Sort the filtered chats
            $mergedChats = $filteredChats->sortBy(function ($chat) {
                return [$chat->created_at, $chat->name, -$chat->id];
            });
            $refers = $mergedChats->where('isbot', '=', true);

            // Initialize the result array
            $chatMessages = [];

            // Iterate over each chat message
            foreach ($mergedChats->select('msg', 'name', 'isbot') as $chat) {
                $user = $chat['isbot'] ? $chat['name'] : request()->user()->name;
                $chatMessages[] = ['user' => $user, 'message' => $chat['msg']];
            }

            // Create a new PhpWord object
            $phpWord = new PhpWord();

            // Add a new section to the document
            $section = $phpWord->addSection();

            // Set font style for "user" to always be black
            $styles = [
                'user' => ['name' => 'Tahoma', 'size' => 12, 'color' => '000000'],
            ];

            // Function to generate a random color
            function getRandomColor()
            {
                do {
                    $color = sprintf('%06X', mt_rand(0, 0xffffff));
                } while (hexdec($color) > 0xcccccc); // Ensure the color is not too close to white
                return $color;
            }

            // Define a base style for messages
            $baseStyle = ['name' => 'Tahoma', 'size' => 12];
            // Add chat messages to the document with random colors
            foreach ($chatMessages as $chat) {
                $user = $chat['user'];
                $message = $chat['message'];

                // Assign random color if not already assigned and not "user"
                if (!isset($styles[$user]) && $user !== 'user') {
                    $styles[$user] = ['name' => 'Tahoma', 'size' => 12, 'color' => getRandomColor()];
                }
                // Add the user name with the respective style
                $section->addText("$user:", $user === 'user' ? $userStyle : array_merge($baseStyle, ['bold' => true, 'color' => $styles[$user]['color']]));

                // Split message by new lines and add each line separately
                $messageLines = explode("\n", $message);
                foreach ($messageLines as $line) {
                    $section->addText($line, $styles[$user] ?? $baseStyle);
                }

                // Add space after the message for readability
                $section->addTextBreak(1);
            }

            // Save the document as ODT
            $fileName = ChatRoom::find(request()->route('room_id'))->name . '.odt';
            $objWriter = IOFactory::createWriter($phpWord, 'ODText');
            $objWriter->save(storage_path("$fileName"));

            return response()
                ->download(storage_path("$fileName"))
                ->deleteFileAfterSend(true);
        } else {
            return redirect()->route('room.home');
        }
    }
    public function export_to_pdf(Request $request)
    {
        $chat = ChatRoom::find($request->route('room_id'));
        if ($chat && $chat->user_id == Auth::user()->id) {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            // instantiate and use the dompdf class
            $html = view('room.export_pdf');
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream();
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
            $url = rawurldecode($this->upload_file($request));
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
        $url = rawurldecode($this->upload_file($request));
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
