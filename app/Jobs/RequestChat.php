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
    private $input, $API, $msgtime, $history_id, $user_id, $chat_id;
    /**
     * Create a new job instance.
     */
    public function __construct($history_id, $input, $API, $user_id)
    {
        $this->history_id = $history_id;
        $this->input = $input;
        $this->msgtime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'));
        $this->API = $API;
        $this->user_id = $user_id;
        $this->chat_id = Histories::findOrFail($history_id)->chat_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Redis::set('msg' . $this->history_id, '');
            $client = new Client();
            $response = $client->post("http://localhost:9000/status", [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'name' => "Test",
                    "userid" => $this->user_id
                ],
                'stream' => true,
            ]);
            Log::Debug($response);
            if ($response == "BUSY") {
                $this->release(10);
            }
            $response = $client->post("http://localhost:9000/", [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'input' => $this->input,
                    'name' => "Test",
                    "userid" => $this->user_id
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
                        Redis::set('msg' . $this->history_id, Redis::get('msg' . $this->history_id) . $message);
                        $buffer = mb_substr($buffer, $messageLength, null, 'UTF-8');
                    }
                }
                if (strlen(Redis::get('msg' . $this->history_id)) > 700) {
                    break;
                }
            }
            if (Redis::get('msg' . $this->history_id) == '') {
                Redis::set('msg' . $this->history_id, '[Oops, seems like LLM given empty message as output!]');
            }
        } catch (Exception $e) {
            Redis::set('msg' . $this->history_id, Redis::get('msg' . $this->history_id) . "\n[Sorry, something is broken!]");
        } finally {
            $history = new Histories();
            $history->fill(['msg' => trim(Redis::get('msg' . $this->history_id)), 'chat_id' => $this->chat_id, 'isbot' => true, 'created_at' => $this->msgtime]);
            $history->save();
            Redis::lrem('usertask_' . $this->user_id, 0, $this->history_id);
        }
    }
}
