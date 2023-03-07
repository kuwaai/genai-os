<?php

namespace App\Http\Controllers;

use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use App\Models\APIHistories;
use Illuminate\Http\Request;
use App\Models\Histories;

class apiController extends Controller
{
    function verifyToken(Request $request)
    {
        $token = $request->input('token');
        return response()->json([
            'result' => DB::table('personal_access_tokens')->where('token', $token)->exists(),
        ]);
    }

    function createRecord(Request $request){
        $token = $request->input('token');
        $input = $request->input('input');
        $chat_id = $request->input('chat_id');
        $output = $request->input('output');
        if (!$output || trim($output) == "") $output = "[Sorry, This LLM generate nothing as feedback.]";
        if (!$input || trim($input) == "") return response()->json(['result' => false]);
        try{
            if ($chat_id > 0){
                $history = new Histories();
                $history->fill(['msg' => $output, 'chat_id' => $chat_id, 'isbot' => true]);
                $history->save();
            }else{
                $userID = PersonalAccessToken::findToken($token)->tokenable->id;
                $history = new APIHistories();
                $history->fill(['output' => $output, 'input' => $input, 'user_id' => $userID ]);
                $history->save();
            }
            return response()->json(['result' => true]);
        }catch (Exception $e){
            error_log($e);
            return response()->json(['result' => false]);
        }
    }
}
