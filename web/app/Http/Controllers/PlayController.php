<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;

class PlayController extends Controller
{
    function play(Request $request){
        if (SystemSetting::where('key', 'ai_election_enabled')->first()->value == 'true'){
            return view('play.ai_election');
        }
        return Redirect::route('play.home');
    }

    function update(Request $request){
        $result = "play_setting_saved";
        $model = SystemSetting::where("key", "ai_election_enabled")->first();
        $model->value = $request->input("ai_election_enabled") == "allow" ? "true" : "false";
        $model->save();

        return Redirect::route('manage.home')
        ->with('last_tab', 'settings')
        ->with('last_action', 'update')
        ->with('status', $result);
    }
}
