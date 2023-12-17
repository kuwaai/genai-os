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
            ->map(function ($access_codes) {
                return $access_codes->groupby('user_id')->map(function ($userHistories) {
                    return $userHistories->groupBy('id')->map(function ($groupedHistories) {
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
                                if ($record->nice) {
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
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        file_put_contents('exported_data.json', $jsonContent);

        $this->info('Data exported to exported_data.json');
    }
}
