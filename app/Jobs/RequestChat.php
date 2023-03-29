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

class RequestChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $timeout = 600; # 10 mins limit
    private $input, $API;
    public $chat_id;

    /**
     * Create a new job instance.
     */
    public function __construct($chat_id, $input, $API)
    {
        $this->chat_id = $chat_id;
        $this->input = $input;
        $this->API = $API;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Redis::set($this->chat_id, '');
            Redis::set($this->chat_id . 'status', 'running');
            $client = new Client();
            $response = $client->post($this->API, [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'input' => $this->input,
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
                    if (mb_check_encoding($message, 'UTF-8')){
                        Redis::set($this->chat_id, Redis::get($this->chat_id) . $message);
                        $buffer = mb_substr($buffer, $messageLength, null, 'UTF-8');
                    }
                }
            }
            if (Redis::get($this->chat_id) == '') {
                Redis::set($this->chat_id, '[Oops, seems like LLM given empty message as output!]');
            }
        } catch (Exception $e) {
            Redis::set($this->chat_id, Redis::get($this->chat_id) . "\n[Sorry, something is broken!]");
        } finally {
            $history = new Histories();
            $history->fill(['msg' => trim(Redis::get($this->chat_id)), 'chat_id' => $this->chat_id, 'isbot' => true]);
            $history->save();
            Redis::set($this->chat_id . 'status', 'finished');
        }
    }
}
