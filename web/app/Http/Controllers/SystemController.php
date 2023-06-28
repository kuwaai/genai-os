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
        $result = "setting_saved";
        $model = SystemSetting::where("key", "agent_location")->first();
        $model->value = $request->input("agent_location");
        $model->save();

        if ($request->input("allow_register") == "allow"){
            if (in_array(null, [env("MAIL_MAILER", null), env("MAIL_HOST", null), env("MAIL_PORT", null), env("MAIL_USERNAME", null), env("MAIL_PASSWORD", null), env("MAIL_ENCRYPTION", null), env("MAIL_FROM_ADDRESS", null), env("MAIL_FROM_NAME", null)])){
                $request->merge(['allow_register' => null]);
                $result = "smtp_not_configured";
            }
        }

        $model = SystemSetting::where("key", "allowRegister")->first();
        $model->value = $request->input("allow_register") == "allow" ? "true" : "false";
        $model->save();

        return Redirect::route('dashboard.home')->with('status', $result);
    }
}
