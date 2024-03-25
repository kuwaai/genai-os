<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Histories;
use SplFileObject;

class ExportTrainData extends Command
{
    protected $signature = 'export:traindata {file? : The path to the JSON file}';

    protected $description = 'Export raw data from database or parse json file into training data';

    public function handle()
    {
        $file = $this->argument('file');
        if ($file) {
            $trainData = [];
            $invalidContents = ["[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]", '[Oops, the LLM returned empty message, please try again later or report to admins!]', '[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]'];
            foreach (json_decode(file_get_contents($file)) as $key => $value) {
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
            $handle = fopen('converted_data.jsonl', 'w');
            if ($handle !== false) {
                foreach ($trainData as $item) {
                    fwrite($handle, json_encode($item, JSON_UNESCAPED_UNICODE) . "\n");
                }
                fclose($handle);
                $this->info('Data exported to converted_data.jsonl');
            } else {
                $this->info('Unable to open file for writing.');
            }
        } else {
            $this->exportToJson();
        }
    }

    private function exportToJson()
    {
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
        file_put_contents('exported_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('Data exported to exported_data.json');
    }
}
