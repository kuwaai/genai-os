<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use App\Models\Histories;
use App\Jobs\ImportChat;
use App\Jobs\RequestChat;
use App\Models\Chats;
use App\Models\LLMs;
use App\Models\Bots;
use App\Models\User;
use App\Models\Feedback;
use DB;
use Session;

class BotController extends Controller
{
    public function home(Request $request)
    {
        return view('store');
    }
    public function create(Request $request)
    {
        $model_id = LLMs::where('name', '=', $request->input('llm_name'))->first()->id;
        if ($model_id) {
            $bot = new Bots();
            $bot->fill(['name' => $request->input('bot-name'), 'type' => 'prompt', 'visibility' => 1, "description"=>$request->input('bot-describe'),"model_id"=>$model_id, "config"=>null]); // 1 for public
            $bot->save();
            $request->input('startup-prompt');
            $request->input('welcome-message');
        }

        return redirect()->route('store.home');
    }
    public function update(Request $request)
    {
        
        return redirect()->route('store.home');
    }
}
