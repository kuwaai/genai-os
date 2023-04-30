<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;

class SystemController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $model = SystemSetting::where("key", "agent_location")->first();
        $model->value = $request->input("agent_location");
        $model->save();
        return Redirect::route('dashboard')->with('status', 'agent_location-updated');
    }
}
