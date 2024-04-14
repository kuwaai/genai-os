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
use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Events\RequestStatus;
use App\Models\Histories;
use GuzzleHttp\Client;
use Carbon\Carbon;

class RequestChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $input, $access_code, $msgtime, $history_id, $user_id, $channel, $openai_token, $google_token, $user_token;
    public $tries = 100; # Wait 1000 seconds in total
    public $timeout = 1200; # For the 100th try, 200 seconds limit is given
    public static $agent_version = 'v1.0';
    public $filters = ["[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]", '[Oops, the LLM returned empty message, please try again later or report to admins!]', '[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]', '[Sorry, something is broken, please try again later!]'];

    /**
     * Create a new job instance.
     */
    public function __construct($input, $access_code, $user_id, $history_id, $channel = null)
    {
        $this->input = json_encode(json_decode($input), JSON_UNESCAPED_UNICODE);
        $this->msgtime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'));
        $this->access_code = $access_code;
        $this->user_id = $user_id;
        $this->history_id = $history_id;
        if ($channel == null) {
            $channel = '';
        }
        $this->channel = $channel;
        $user = User::find($user_id);
        $this->openai_token = $user->openai_token;
        $this->google_token = $user->google_token;
        if ($user->tokens()->where('name', 'API_Token')->count() != 1) {
            $user->tokens()->where('name', 'API_Token')->delete();
            $user->createToken('API_Token', ['access_api']);
        }
        $this->user_token = $user->tokens()->where('name', 'API_Token')->first()->token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $warningMessages = [];
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
            $response = $client->post($agent_location . '/' . self::$agent_version . '/worker/schedule', [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'name' => $this->access_code,
                    'history_id' => $this->history_id * ($this->channel == $this->history_id ? 1 : -1),
                    'user_id' => $this->user_id,
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

                Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                Redis::publish($this->channel, 'Ended Ended');
                $msgTimeInSeconds = Carbon::createFromFormat('Y-m-d H:i:s', $this->msgtime)->timestamp;
                $currentTimeInSeconds = Carbon::now()->timestamp;
                $ExecutionTime = $currentTimeInSeconds - $msgTimeInSeconds;

                if ($ExecutionTime < 5) {
                    sleep(5 - $ExecutionTime);
                }
                Redis::lrem(($this->channel == $this->history_id ? 'usertask_' : 'api_') . $this->user_id, 0, $this->history_id);

                Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                Redis::publish($this->channel, 'Ended Ended');
            } elseif ($state == 'READY') {
                try {
                    $test = json_decode($this->input);

                    if ($test === false && json_last_error() !== JSON_ERROR_NONE) {
                        //There're error in the json!
                        //which shouldn't be happening...
                        Log::channel('analyze')->Info("How does that happened? JSON decode error in the Job!\n" . $this->input);
                        return;
                    } else {
                        $test_2 = collect(json_decode($this->input))
                            ->where('isbot', false)
                            ->last();
                        if ($test_2 !== null) {
                            $taide_flag = strpos(strtoupper($test_2->msg), strtoupper('taide')) !== false;

                            foreach ($test as $t) {
                                foreach ($this->filters as $filter) {
                                    if (strpos($t->msg, $filter) !== false) {
                                        $t->msg = trim(str_replace($filter, '', $t->msg));
                                    }
                                }
                                if ($t->isbot) {
                                    $t->msg = preg_replace('#<<<WARNING>>>.*?<<</WARNING>>>#s', '', $t->msg);
                                }
                            }
                            $this->input = json_encode($test);
                        } else {
                            $taide_flag = false;
                        }
                        if (trim(\App\Models\SystemSetting::where('key', 'safety_guard_location')->first()->value) !== '') {
                            $taide_flag = false;
                        }
                    }
                    $response = $client->post($agent_location . '/' . self::$agent_version . '/chat/completions', [
                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                        'form_params' => [
                            'input' => $this->input,
                            'name' => $this->access_code,
                            'user_id' => $this->user_id,
                            'history_id' => $this->history_id * ($this->channel == $this->history_id ? 1 : -1),
                            'openai_token' => $this->openai_token,
                            'google_token' => $this->google_token,
                            'user_token' => $this->user_token,
                        ],
                        'stream' => true,
                    ]);
                    $stream = $response->getBody();
                    $buffer = '';
                    $insideTag = false;
                    $cache = false;
                    $cached = '';
                    $tmp = '';
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
                                if ($this->channel != $this->history_id) {
                                    $tmp .= $message;
                                    Redis::publish($this->channel, 'New ' . json_encode(['msg' => $message]));
                                    $buffer = mb_substr($buffer, $messageLength, null, 'UTF-8');
                                } else {
                                    if ($message === '<' && !$cache) {
                                        $cache = true;
                                    }
                                    if (!$cache) {
                                        $tmp .= $message;
                                        $outputTmp = $tmp . '...';
                                        if ($taide_flag) {
                                            $outputTmp .= "\n\n[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]";
                                        }
                                        if ($warningMessages) {
                                            $outputTmp .= '<<<WARNING>>>' . implode("\n", $warningMessages) . '<<</WARNING>>>';
                                        }

                                        Redis::publish($this->channel, 'New ' . json_encode(['msg' => $outputTmp]));
                                    } else {
                                        //start caching
                                        $cached .= $message;
                                        if (!(strpos('<<<WARNING>>>', $cached) !== false || strpos($cached, '<<<WARNING>>>') !== false)) {
                                            $cache = false;
                                            $tmp .= $cached;
                                            $outputTmp = $tmp;
                                            if ($this->channel == $this->history_id) {
                                                $outputTmp .= '...';
                                            }
                                            if ($taide_flag && $this->channel == $this->history_id) {
                                                $outputTmp .= "\n\n[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]";
                                            }
                                            if ($warningMessages) {
                                                $outputTmp .= '<<<WARNING>>>' . implode("\n", $warningMessages) . '<<</WARNING>>>';
                                            }

                                            if ($this->channel != $this->history_id) {
                                                // Loop over each character in the UTF-8 string
                                                for ($i = 0; $i < mb_strlen($outputTmp, 'UTF-8'); $i++) {
                                                    // Get the current character
                                                    $char = mb_substr($outputTmp, $i, 1, 'UTF-8');
                                                    // Publish the character to Redis
                                                    Redis::publish($this->channel, 'New ' . json_encode(['msg' => $char]));
                                                }
                                            } else {
                                                Redis::publish($this->channel, 'New ' . json_encode(['msg' => $outputTmp]));
                                            }
                                            $cached = '';
                                        } elseif ($message === '>' && (str_ends_with($cached, '<<</WARNING>>>') || str_ends_with($cached, '<<<\/WARNING>>>'))) {
                                            $warningMessages[] = trim(str_replace(['<<<WARNING>>>', '<<</WARNING>>>', '<<<\/WARNING>>>'], '', $cached));
                                            $cache = false;
                                            $cached = '';
                                        }
                                    }
                                    $buffer = mb_substr($buffer, $messageLength, null, 'UTF-8');
                                }
                            }
                        }
                        /*if (mb_strlen($tmp) > 3500) {
                            break;
                        }*/
                    }

                    if (trim($tmp) == '' && empty($warningMessages)) {
                        $tmp = '[Oops, the LLM returned empty message, please try again later or report to admins!]';
                    } else {
                        if ($this->channel != $this->history_id) {
                            Redis::publish($this->channel, 'Ended Ended');
                        } elseif ($taide_flag) {
                            $tmp .= "\n\n[有關TAIDE計畫的相關說明，請以 taide.tw 官網的資訊為準。]";
                        }
                    }
                } catch (Exception $e) {
                    if ($this->channel != $this->history_id) {
                        $text = '\n[Sorry, something is broken, please try again later!]';
                        // Loop over each character in the UTF-8 string
                        for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
                            // Get the current character
                            $char = mb_substr($text, $i, 1, 'UTF-8');
                            // Publish the character to Redis
                            Redis::publish($this->channel, 'New ' . json_encode(['msg' => $char]));
                        }
                    } else {
                        Redis::publish($this->channel, 'New ' . json_encode(['msg' => $tmp . '\n[Sorry, something is broken, please try again later!]']));
                    }
                    $tmp .= "\n[Sorry, something is broken, please try again later!]";

                    Log::channel('analyze')->Debug('failJob ' . $this->history_id);
                } finally {
                    try {
                        if ($this->channel == $this->history_id) {
                            $history = Histories::find($this->history_id);
                            if ($history != null) {
                                $result = trim(preg_replace('#<<<WARNING>>>.*?<<</WARNING>>>#s', '', $tmp));
                                if ($warningMessages) {
                                    $result .= '<<<WARNING>>>' . implode("\n", $warningMessages) . '<<</WARNING>>>';
                                }
                                $history->fill(['msg' => $result]);
                                $history->save();
                                $tmp = $result;
                            }
                        }
                    } catch (Exception $e) {
                    }

                    $end = microtime(true);
                    $elapsed = $end - $start;
                    Log::channel('analyze')->Info('Out:' . $this->access_code . '|' . $this->user_id . '|' . $this->history_id . '|' . $elapsed . '|' . strlen(trim($tmp)) . '|' . Carbon::createFromFormat('Y-m-d H:i:s', $this->msgtime)->diffInSeconds(Carbon::now()) . '|' . $tmp);

                    if ($this->channel == $this->history_id) {
                        $msgTimeInSeconds = Carbon::createFromFormat('Y-m-d H:i:s', $this->msgtime)->timestamp;
                        $currentTimeInSeconds = Carbon::now()->timestamp;
                        $ExecutionTime = $currentTimeInSeconds - $msgTimeInSeconds;
                        while ($ExecutionTime < 2) {
                            Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                            Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                            Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                            Redis::publish($this->channel, 'Ended Ended');
                            $currentTimeInSeconds = Carbon::now()->timestamp;
                            $ExecutionTime = $currentTimeInSeconds - $msgTimeInSeconds;
                        }
                        Redis::lrem(($this->channel == $this->history_id ? 'usertask_' : 'api_') . $this->user_id, 0, $this->history_id);
                        Redis::publish($this->channel, 'New ' . json_encode(['msg' => trim($tmp)]));
                        Redis::publish($this->channel, 'Ended Ended');
                    }else{
                        Redis::lrem(($this->channel == $this->history_id ? 'usertask_' : 'api_') . $this->user_id, 0, $this->history_id);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::channel('analyze')->Info('Failed job: ' . $this->channel);
            Log::channel('analyze')->Info($e->getMessage());
            $history = Histories::find($this->history_id);
            if ($history != null) {
                $history->fill(['msg' => '[Sorry, something is broken, please try again later!]']);
                $history->save();
            }
            Redis::publish($this->channel, 'New ' . json_encode(['msg' => '[Sorry, something is broken, please try again later!]']));
            Redis::publish($this->channel, 'Ended Ended');
            Redis::lrem(($this->channel == $this->history_id ? 'usertask_' : 'api_') . $this->user_id, 0, $this->history_id);

            $msgTimeInSeconds = Carbon::createFromFormat('Y-m-d H:i:s', $this->msgtime)->timestamp;
            $currentTimeInSeconds = Carbon::now()->timestamp;
            $ExecutionTime = $currentTimeInSeconds - $msgTimeInSeconds;

            if ($ExecutionTime < 5) {
                sleep(5 - $ExecutionTime);
            }

            Redis::publish($this->channel, 'New ' . json_encode(['msg' => '[Sorry, something is broken, please try again later!]']));
            Redis::publish($this->channel, 'Ended Ended');
        }
    }
    public function failed(\Throwable $exception)
    {
        if ($this->channel == '') {
            $this->channel .= $this->history_id;
        }
        Log::channel('analyze')->Info('Failed job: ' . $this->channel);

        $history = Histories::find($this->history_id);
        if ($history != null) {
            $history->fill(['msg' => '[Sorry, something is broken, please try again later!]']);
            $history->save();
        }
        Redis::lrem(($this->channel == $this->history_id ? 'usertask_' : 'api_') . $this->user_id, 0, $this->history_id);

        Redis::publish($this->channel, 'New ' . json_encode(['msg' => '[Sorry, something is broken, please try again later!]']));
        Redis::publish($this->channel, 'Ended Ended');
    }
}
