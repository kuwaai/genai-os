<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;

class ElectionController extends Controller
{
    function home(Request $request){
        
        return view('ai_election.home');
    }
}
