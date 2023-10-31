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
use App\Models\Histories;
use App\Models\User;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;

class ImportChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $ids, $access_code, $user_id;
    public $tries = 1; # Believe if it fails, it always failed
    public $timeout = 600; # Shouldn't takes longer than 10 mins
    public $agent_version = 'v1.0';
    /**
     * Create a new job instance.
     */
    public function __construct($ids, $access_code, $user_id)
    {
        $this->ids = $ids;
        $this->access_code = $access_code;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->ids as $id) {
            $history = Histories::find($id);
            $input = Histories::where('chat_id', '=', $history->chat_id)
                ->where('id', '<', $id)
                ->select('msg', 'isbot');
            if (Histories::find($id)->chained) {
                $input = $input->orderby('created_at')->orderby('id', 'desc');
            } else {
                $input = $input
                    ->orderby('created_at', 'desc')
                    ->orderby('id')
                    ->limit(1);
            }
            $input = $input->get()->toJson();
            RequestChat::dispatchSync($input, $this->access_code, $this->user_id, $id, User::find($this->user_id)->openai_token);
        }
    }
}
