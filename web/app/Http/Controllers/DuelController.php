<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Jobs\RequestChat;
use App\Models\DuelChat;
use App\Models\Histories;
use App\Models\Chats;
use App\Models\LLMs;

class DuelController extends Controller
{
    public function main(Request $request)
    {
        $duel_id = $request->route('duel_id');
        if ($duel_id){
            $chat = DuelChat::findOrFail($duel_id);
            if ($chat->user_id != Auth::user()->id) {
                return redirect()->route('duel.home');
            } elseif (true){#LLMs::findOrFail($chat->llm_id)->enabled == true) {
                return view('duel');
            }
        }
        return view('duel');
        #return redirect()->route('archives', $request->route('chat_id'));
    }

    public function create(Request $request): RedirectResponse
    {
        $llms = $request->input('llm');
        if (count($llms) > 1) {
            $Duel = new DuelChat();
            $Duel->fill(['name' => "新聊天室", 'user_id' => Auth::user()->id]);
            $Duel->save();
            foreach ($llms as $llm){
                $LLM = LLMs::where("access_code", $llm)->first();
                $chat = new Chats();
                $chat->fill(['name' => "Duel Chat", 'llm_id' => $LLM->id, 'user_id' => Auth::user()->id, "dcID"=>$Duel->id]);
                $chat->save();
            }
        }
        return redirect()->to(route('duel.chat', $Duel->id) . ($request->input('limit') ? "?limit=" . $request->input('limit') : ""));
    }
    public function delete(Request $request): RedirectResponse
    {
        try {
            $chat = DuelChat::findOrFail($request->input('id'));
            $chat->delete();
        } catch (ModelNotFoundException $e) {
            Log::error("Chat not found: " . $request->input('id'));
        }
        return redirect()->to(route('duel.home') . ($request->input('limit') ? "?limit=" . $request->input('limit') : ""));
    }

    public function edit(Request $request): RedirectResponse
    {
        try {
            $chat = DuelChat::findOrFail($request->input('id'));
            $chat->fill(['name' => $request->input('new_name')]);
            $chat->save();
        } catch (ModelNotFoundException $e) {
            Log::error("Chat not found: " . $request->input('id'));
        }
        return redirect()->to(route('duel.chat', $request->input('id')) . ($request->input('limit') ? "?limit=" . $request->input('limit') : ""));
    }
    
    public function request(Request $request): RedirectResponse
    {
        $duelId = $request->input('duel_id');
        $input = $request->input('input');
        if ($duelId && $input) {
            $chats = Chats::where("dcID",$request->input('duel_id'))->get();
            if (Chats::join("llms", 'llms.id', '=', 'llm_id')->where("dcID",$request->input('duel_id'))->get()->where("enabled",false)->count() == 0){
                foreach ($chats as $chat) {
                    $history = new Histories();
                    $history->fill(['msg' => $input, 'chat_id' => $chat->id, 'isbot' => false]);
                    $history->save();
                    $history = new Histories();
                    $history->fill(['msg' => "* ...thinking... *", 'chat_id' => $chat->id, 'isbot' => true, 'created_at' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 second'))]);
                    $history->save();
                    RequestChat::dispatch($chat->id, $input, LLMs::findOrFail($chat->llm_id)->access_code, Auth::user()->id, $history->id, Auth::user()->openai_token);
                    Redis::rpush('usertask_' . Auth::user()->id, $history->id);
                }
            }
        }
        return redirect()->to(route('duel.chat', $duelId) . ($request->input('limit') ? "?limit=" . $request->input('limit') : ""));
    }
}