<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $timeout = 600 # 10 mins limit
	
	protected $token, $chat_id, $input;

    /**
     * Create a new job instance.
     */
    public function __construct($token, $chat_id, $input)
    {
        $this->token = $token;
        $this->chat_id = $chat_id;
        $this->input = $input;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // DO MY WORK HERE
    }
}
