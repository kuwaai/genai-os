<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\Histories;
use Illuminate\Support\Facades\Storage;
use App\Models\Logs;

class DashboardController extends Controller
{
    function home(Request $request)
    {
        if ($request->input('tab')) {
            return Redirect::route('dashboard.home')
                ->with('last_tab', $request->input('tab'))
                ->with('page', $request->input('page'))
                ->with('start_date', $request->input('start_date'))
                ->with('end_date', $request->input('end_date'))
                ->with('action', $request->input('action'))
                ->with('description', $request->input('description'))
                ->with('user_id', $request->input('user_id'))
                ->with('ip_address', $request->input('ip_address'));
        }
        return view('dashboard.home');
    }
    function feedback(Request $request)
    {
        try {
            if ($request->input('rawdata')) {
                $trainData = [];
                $invalidContents = ["[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]", '[Oops, the LLM returned empty message, please try again later or report to admins!]', '[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]'];
                try {
                    foreach (json_decode($request->input('rawdata')) as $key => $value) {
                        //model record
                        foreach ($value as $innerKey => $innerValue) {
                            //user record
                            foreach ($innerValue as $innerInnerKey => $innerInnerValue) {
                                // chat record
                                $roles = [];
                                $histories = [];
                                foreach ($innerInnerValue as $item) {
                                    $item->content = trim(str_replace($invalidContents, '', $item->content));
                                    if ($item->content !== '') {
                                        //History record
                                        if ($item->role == 'assistant' && isset($item->feedback) && isset($item->feedback->nice)) {
                                            //As the response exist, there should be a prompt to generate it
                                            $tmp = null;
                                            if ($item->chain && $histories !== []) {
                                                $count = min(count($histories), count($roles));
                                                $tmp = ['prompt' => ''];
                                                for ($i = 0; $i < $count; $i++) {
                                                    $tmp['prompt'] .= "$roles[$i] $histories[$i] ";
                                                }
                                                $tmp['prompt'] = trim($tmp['prompt']) . ' Assistant:';
                                            } else {
                                                $tmp = ['prompt' => 'Human: ' . trim(end($histories)) . ' Assistant:'];
                                            }
                                            if ($item->feedback->nice) {
                                                $tmp['response'] = $item->content;
                                                $tmp['chosen'] = $item->content;
                                                $tmp['rejected'] = '';
                                            } else {
                                                $tmp['response'] = '';
                                                $tmp['chosen'] = '';
                                                $tmp['rejected'] = $item->content;
                                            }
                                            $trainData[] = $tmp;
                                        }
                                        $histories[] = $item->content;
                                        $roles[] = $item->role == 'user' ? 'Human:' : 'Assistant:';
                                    }
                                }
                            }
                        }
                    }

                    $fileContents = '';
                    foreach ($trainData as $item) {
                        $fileContents .= json_encode($item, JSON_UNESCAPED_UNICODE) . "\n";
                    }
                    $fileName = 'converted_data.jsonl';

                    // Store the JSON file in storage
                    Storage::put($fileName, trim($fileContents));

                    // Retrieve the file path
                    $filePath = storage_path('app/' . $fileName);

                    // Return the file as a downloadable response
                    return response()
                        ->download($filePath)
                        ->deleteFileAfterSend(true);
                } catch (\Throwable) {
                }
                return Redirect::route('dashboard.home')
                    ->with('last_tab', 'feedbacks')
                    ->with('rawdata', $request->input('rawdata'))
                    ->with('status', 'rawdata-error');
            } elseif ($request->input('models')) {
                $data = Histories::withTrashed()
                    ->Join('chats', 'histories.chat_id', '=', 'chats.id')
                    ->Join('llms', 'llm_id', '=', 'llms.id')
                    ->leftjoin('feedback', 'histories.id', '=', 'history_id')
                    ->select('histories.msg as content', 'histories.created_at', 'histories.updated_at', 'histories.deleted_at', 'histories.chained as chain', 'histories.chat_id as id', 'histories.isbot', 'chats.user_id', 'histories.id as history_id', 'llms.access_code', 'feedback.detail', 'feedback.flags', 'feedback.nice')
                    ->orderby('access_code')
                    ->orderby('user_id')
                    ->orderby('id')
                    ->orderby('created_at')
                    ->orderby('histories.id', 'desc')
                    ->whereIn('llm_id', $request->input('models'))
                    ->get()
                    ->groupBy('access_code', 'user_id', 'id')
                    ->filter(function ($access_codes) {
                        return $access_codes
                            ->groupby('user_id')
                            ->map(function ($userHistories) {
                                return $userHistories
                                    ->groupBy('id')
                                    ->map(function ($groupedHistories) {
                                        $filteredHistories = $groupedHistories->filter(function ($record) {
                                            return $record->nice !== null;
                                        });
                                        return $filteredHistories->isNotEmpty();
                                    })
                                    ->contains(true);
                            })
                            ->contains(true);
                    })
                    ->map(function ($access_codes) {
                        return $access_codes
                            ->groupby('user_id')
                            ->filter(function ($userHistories) {
                                $filtered = $userHistories->filter(function ($record) {
                                    return $record->nice !== null;
                                });
                                return $filtered->isNotEmpty();
                            })
                            ->map(function ($userHistories) {
                                return $userHistories
                                    ->groupBy('id')
                                    ->filter(function ($groupedHistories) {
                                        $filtered = $groupedHistories->filter(function ($record) {
                                            return $record->nice !== null;
                                        });
                                        return $filtered->isNotEmpty();
                                    })
                                    ->map(function ($groupedHistories) {
                                        return $groupedHistories
                                            ->map(function ($record) {
                                                $raw = [
                                                    'content' => $record->content,
                                                    'id' => $record->history_id,
                                                    'role' => $record->isbot ? 'assistant' : 'user',
                                                    'created_at' => strtotime($record->created_at),
                                                    'updated_at' => strtotime($record->updated_at),
                                                    'deleted_at' => strtotime($record->deleted_at),
                                                    'chain' => $record->chain,
                                                ];
                                                if ($record->nice !== null) {
                                                    $tmp = [];
                                                    $tmp['nice'] = $record->nice;
                                                    if ($record->flags) {
                                                        $tmp['flags'] = json_decode($record->flags);
                                                    }
                                                    if ($record->detail) {
                                                        $tmp['detail'] = $record->detail;
                                                    }
                                                    $raw['feedback'] = $tmp;
                                                }
                                                return $raw;
                                            })
                                            ->toArray();
                                    });
                            });
                    });
                $fileContents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $fileName = 'exported_data.json';

                // Store the JSON file in storage
                Storage::put($fileName, $fileContents);

                // Retrieve the file path
                $filePath = storage_path('app/' . $fileName);

                // Return the file as a downloadable response
                return response()
                    ->download($filePath)
                    ->deleteFileAfterSend(true);
            }
        } catch (\Throwable) {
        }
        return Redirect::route('dashboard.home')->with('last_tab', 'feedbacks');
    }
}
