<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Redis;

class SystemController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $result = "success";
        $model = SystemSetting::where("key", "agent_location")->first();
        $model->value = $request->input("agent_location");
        $model->save();

        if ($request->input("allow_register") == "allow"){
            if (in_array(null, [config("app.MAIL_MAILER"), config("app.MAIL_HOST"), config("app.MAIL_PORT"), config("app.MAIL_USERNAME"), config("app.MAIL_PASSWORD"), config("app.MAIL_ENCRYPTION"), config("app.MAIL_FROM_ADDRESS"), config("app.MAIL_FROM_NAME")])){
                $request->merge(['allow_register' => null]);
                $result = "smtp_not_configured";
            }
        }

        $model = SystemSetting::where("key", "allowRegister")->first();
        $model->value = $request->input("allow_register") == "allow" ? "true" : "false";
        $model->save();

        return Redirect::route('manage.home')
                ->with('last_tab', 'settings')
                ->with('last_action', 'update')
                ->with('status', $result);
    }

    
    public function ResetRedis(Request $request)
    {
        Redis::flushAll();
        return Redirect::route('manage.home')
                ->with('last_tab', 'settings')
                ->with('last_action', 'resetRedis')
                ->with('status', 'success');
    }
}
