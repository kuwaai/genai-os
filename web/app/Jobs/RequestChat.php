<?php

namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use App\Events\RequestStatus;
use App\Models\Histories;
use GuzzleHttp\Client;
use Carbon\Carbon;

class RequestChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $input, $access_code, $msgtime, $history_id, $user_id, $chatgpt_apitoken, $channel;
    public $tries = 100; # Wait 1000 seconds in total
    public $timeout = 1200; # For the 100th try, 200 seconds limit is given
    public $agent_version = 'v1.0';
    public $filters = ["[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]", '[Oops, the LLM returned empty message, please try again later or report to admins!]', '[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]', '[Sorry, something is broken, please try again later!]'];

    /**
     * Create a new job instance.
     */
    public function __construct($input, $access_code, $user_id, $history_id, $chatgpt_apitoken, $channel = null)
    {
        $this->input = $input;
        $this->msgtime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'));
        $this->access_code = $access_code;
        $this->user_id = $user_id;
        $this->history_id = $history_id;
        if ($chatgpt_apitoken == null) {
            $chatgpt_apitoken = '';
        }
        if ($channel == null) {
            $channel = '';
        }
        $this->channel = $channel;
        $this->chatgpt_apitoken = $chatgpt_apitoken;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->channel == '') {
            $this->channel .= $this->history_id;
        }
        Log::channel('analyze')->Info($this->channel);
        if ($this->history_id > 0 && $this->channel == $this->history_id . '') {
            if (Histories::find($this->channel) && Histories::find($this->channel)->msg != '* ...thinking... *') {
                Log::Debug('Hmmm');
                return;
            }
        }
        Log::channel('analyze')->Info('In:' . $this->access_code . '|' . $this->user_id . '|' . $this->history_id . '|' . strlen(trim($this->input)) . '|' . trim($this->input));
        $start = microtime(true);
        $tmp = '';
        try {
            $agent_location = \App\Models\SystemSetting::where('key', 'agent_location')->first()->value;
            $client = new Client(['timeout' => 300]);
            $response = $client->post($agent_location . $this->agent_version . '/worker/schedule', [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'name' => $this->access_code,
                    'history_id' => $this->history_id,
                ],
                'stream' => true,
            ]);
            $state = trim($response->getBody()->getContents());
            if ($state == 'BUSY') {
                $this->release(10);
            } elseif ($state == 'NOMACHINE') {
                $tmp = "[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]";
                try {
                    if ($this->channel == '' . $this->history_id) {
                        $history = Histories::find($this->history_id);
                        if ($history != null) {
                            $history->fill(['msg' => $tmp]);
                            $history->save();
                        }
                    }
                } catch (Exception $e) {
                }
                Log::channel('analyze')->Info('NOMACHINE: ' . $this->access_code . ' | ' . $this->history_id . '|' . strlen(trim($this->input)) . '|' . trim($this->input));
                Redis::lrem('usertask_' . $this->user_id, 0, $this->history_id);
                sleep(1);
                Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                Redis::publish($this->channel, 'Ended Ended');
            } elseif ($state == 'READY') {
                try {
                    $tmp = json_decode($this->input);

                    if ($tmp === false && json_last_error() !== JSON_ERROR_NONE) {
                        //There're error in the json!
                        //which shouldn't be happening...
                        Log::channel('analyze')->Info("How does that happened? JSON decode error in the Job!\n" . $this->input);
                        return;
                    } else {
                        $taide_flag =
                            strpos(
                                strtoupper(
                                    collect(json_decode($this->input))
                                        ->where('isbot', false)
                                        ->last()->msg,
                                ),
                                strtoupper('taide'),
                            ) !== false;

                        foreach ($tmp as $t) {
                            foreach ($this->filters as $filter) {
                                if (strpos($t->msg, $filter) !== false) {
                                    $t->msg = trim(str_replace($filter, '', $t->msg));
                                }
                            }
                        }
                        $this->input = json_encode($tmp);
                        $tmp = '';
                    }

                    $response = $client->post($agent_location . $this->agent_version . '/chat/completions', [
                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                        'form_params' => [
                            'input' => $this->input,
                            'name' => $this->access_code,
                            'history_id' => $this->history_id,
                            'chatgpt_apitoken' => $this->chatgpt_apitoken,
                        ],
                        'stream' => true,
                    ]);
                    $stream = $response->getBody();
                    $buffer = '';
                    while (!$stream->eof()) {
                        $chunk = $stream->read(1);
                        $buffer .= $chunk;
                        $bufferLength = mb_strlen($buffer, 'UTF-8');
                        $messageLength = null;
                        for ($i = 1; $i <= $bufferLength; $i++) {
                            if (ord($buffer[$i - 1]) < 128 || $i == $bufferLength) {
                                $messageLength = $i;
                                break;
                            }
                        }
                        if ($messageLength !== null) {
                            $message = mb_substr($buffer, 0, $messageLength, 'UTF-8');
                            if (mb_check_encoding($message, 'UTF-8')) {
                                $tmp .= $message;
                                Redis::publish($this->channel, 'New ' . json_encode(['msg' => $tmp . '...' . ($taide_flag ? "\n\n[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]" : '')]));
                                $buffer = mb_substr($buffer, $messageLength, null, 'UTF-8');
                            }
                        }
                        if (mb_strlen($tmp) > 3500) {
                            break;
                        }
                    }
                    if (trim($tmp) == '') {
                        $tmp = '[Oops, the LLM returned empty message, please try again later or report to admins!]';
                    } else {
                        if ($taide_flag) {
                            $tmp .= "\n\n[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]";
                        }
                    }
                } catch (Exception $e) {
                    Redis::publish($this->channel, 'New ' . json_encode(['msg' => $tmp . '\n[Sorry, something is broken, please try again later!]']));
                    $tmp .= "\n[Sorry, something is broken, please try again later!]";
                    Log::channel('analyze')->Debug('failJob ' . $this->history_id);
                } finally {
                    try {
                        if ($this->channel == '' . $this->history_id) {
                            $history = Histories::find($this->history_id);
                            if ($history != null) {
                                $history->fill(['msg' => trim($tmp)]);
                                $history->save();
                            }
                        }
                    } catch (Exception $e) {
                    }
                    if ($this->channel == '' . $this->history_id) {
                        Redis::lrem('usertask_' . $this->user_id, 0, $this->history_id);
                    }
                    Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                    Redis::publish($this->channel, 'Ended Ended');
                    $end = microtime(true); // Record end time
                    $elapsed = $end - $start; // Calculate elapsed time
                    Log::channel('analyze')->Info('Out:' . $this->access_code . '|' . $this->user_id . '|' . $this->history_id . '|' . $elapsed . '|' . strlen(trim($tmp)) . '|' . Carbon::createFromFormat('Y-m-d H:i:s', $this->msgtime)->diffInSeconds(Carbon::now()) . '|' . $tmp);
                }
            }
        } catch (Exception $e) {
        }
    }
}
