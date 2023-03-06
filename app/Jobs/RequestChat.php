<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use App\Events\RequestStatus;

class RequestChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $timeout = 600; # 10 mins limit
	
	protected $token, $chat_id, $input, $API;

    /**
     * Create a new job instance.
     */
    public function __construct($token, $chat_id, $input, $API)
    {
        $this->token = $token;
        $this->chat_id = $chat_id;
        $this->input = $input;
        $this->API = $API;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::post($API, [
            'token' => $token,
            'input' => $input,
            'chat_id' => $chat_id
        ]);
        
        $output = json_decode($response->body())->output;

        $userID = PersonalAccessToken::findToken($token)->tokenable->id;
        $history = new APIHistories();
        $history->fill(['output' => $output, 'input' => $input, 'user_id' => $userID ]);
        $history->save();

        event(new RequestStatus($result));
    }
}
