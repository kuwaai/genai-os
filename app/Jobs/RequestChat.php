<?php

namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Bus\Queueable;
use App\Events\RequestStatus;
use App\Models\Histories;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RequestChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $input, $access_code, $msgtime, $history_id, $user_id, $chat_id;
    public $tries = 100; # Wait 1000 seconds in total
    public $timeout = 1200; # For the 100th try, 200 seconds limit is given
    /**
     * Create a new job instance.
     */
    public function __construct($history_id, $input, $access_code, $user_id)
    {
        $this->history_id = $history_id;
        $this->input = $input;
        $this->msgtime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'));
        $this->access_code = $access_code;
        $this->user_id = $user_id;
        $this->chat_id = Histories::findOrFail($history_id)->chat_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel("analyze")->Debug("accessCode " . $this->access_code . " userID " . $this->user_id . " historyID " . $this->history_id . " input " . trim($this->input) . " length " . strlen(trim($this->input)) . " requestTime " . $this->msgtime);
        $start = microtime(true); 
        $tmp = '';
        try {
            $agent_location = \App\Models\SystemSetting::where('key', 'agent_location')->first()->value;
            $client = new Client();
            $response = $client->post($agent_location . 'status', [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'name' => $this->access_code,
                    'history_id' => $this->history_id,
                ],
                'stream' => true,
            ]);
            if ($response->getBody()->getContents() == 'BUSY') {
                $this->release(10);
            } else {
                try {
                    $response = $client->post($agent_location, [
                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                        'form_params' => [
                            'input' => $this->input,
                            'name' => $this->access_code,
                            'history_id' => $this->history_id,
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
                                Redis::publish($this->history_id, 'New ' . $tmp);
                                $buffer = mb_substr($buffer, $messageLength, null, 'UTF-8');
                            }
                        }
                        if (strlen($tmp) > 700) {
                            break;
                        }
                    }
                    if (trim($tmp) == '') {
                        Redis::publish($this->history_id, 'New [Oops, seems like LLM given empty message as output!]');
                    } else {
                        Redis::publish($this->history_id, 'New ' . trim($tmp));
                    }
                } catch (Exception $e) {
                    Redis::publish($this->history_id, 'New ' . $tmp . "\n[Sorry, something is broken!]");
                    Log::channel("analyze")->Debug("failJob " . $this->history_id);
                } finally {
                    try {
                        $history = new Histories();
                        $history->fill(['msg' => trim($tmp), 'chat_id' => $this->chat_id, 'isbot' => true, 'created_at' => $this->msgtime]);
                        $history->save();
                    } catch (Exception $e) {
                    }
                    Redis::publish($this->history_id, 'Ended Ended');
                    Redis::lrem('usertask_' . $this->user_id, 0, $this->history_id);
                    $end = microtime(true); // Record end time
                    $elapsed = $end - $start; // Calculate elapsed time
                    Log::channel("analyze")->Debug("accessCode " . $this->access_code . " userID " . $this->user_id . " historyID " . $this->history_id . " output " . trim(str_replace("\n", "[NEWLINEPLACEHOLDERUWU]", $tmp)) . " took " . $elapsed . " generated " . strlen(trim($tmp)) . " requestTime " . $this->msgtime);
                }
            }
        } catch (Exception $e) {
        }
    }
}
