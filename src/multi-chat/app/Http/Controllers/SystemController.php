<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class SystemController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        function extractBaseUrl($url)
        {
            $parsedUrl = parse_url($url);

            // Check if the URL has a scheme (http, https)
            if (isset($parsedUrl['scheme'])) {
                $baseUrl = $parsedUrl['scheme'] . '://';
            } else {
                $baseUrl = '';
            }

            // Check if the URL has a host (domain)
            if (isset($parsedUrl['host'])) {
                $baseUrl .= $parsedUrl['host'];

                // Include port if present
                if (isset($parsedUrl['port'])) {
                    $baseUrl .= ':' . $parsedUrl['port'];
                }
            }

            return $baseUrl;
        }
        $result = 'success';
        $model = SystemSetting::where('key', 'agent_location')->first();
        $model->value = extractBaseUrl($request->input('agent_location'));
        $model->save();
        $model = SystemSetting::where('key', 'safety_guard_location')->first();
        $model->value = extractBaseUrl($request->input('safety_guard_location'));
        $model->save();

        if ($request->input('allow_register') == 'allow') {
            if (in_array(null, [config('app.MAIL_MAILER'), config('app.MAIL_HOST'), config('app.MAIL_PORT'), config('app.MAIL_USERNAME'), config('app.MAIL_PASSWORD'), config('app.MAIL_ENCRYPTION'), config('app.MAIL_FROM_ADDRESS'), config('app.MAIL_FROM_NAME')])) {
                $request->merge(['allow_register' => null]);
                $result = 'smtp_not_configured';
            }
        }

        if ($request->input('register_need_invite') == 'allow') {
            if (in_array(null, [config('app.MAIL_MAILER'), config('app.MAIL_HOST'), config('app.MAIL_PORT'), config('app.MAIL_USERNAME'), config('app.MAIL_PASSWORD'), config('app.MAIL_ENCRYPTION'), config('app.MAIL_FROM_ADDRESS'), config('app.MAIL_FROM_NAME')])) {
                $request->merge(['register_need_invite' => null]);
                $result = 'smtp_not_configured';
            }
        }
        $model = SystemSetting::where('key', 'allowRegister')->first();
        $model->value = $request->input('allow_register') == 'allow' ? 'true' : 'false';
        $model->save();

        $model = SystemSetting::where('key', 'register_need_invite')->first();
        $model->value = $request->input('register_need_invite') == 'allow' ? 'true' : 'false';
        $model->save();

        $model = SystemSetting::where('key', 'announcement')->first();
        $oldanno = $model->value;
        $model->value = $request->input('announcement') ?? '';
        $model->save();

        if ($oldanno != $model->value) {
            User::query()->update(['announced' => false]);
        }

        $model = SystemSetting::where('key', 'warning_footer')->first();
        $model->value = $request->input('warning_footer') ?? '';
        $model->save();

        $model = SystemSetting::where('key', 'tos')->first();
        $oldtos = $model->value;
        $model->value = $request->input('tos') ?? '';
        $model->save();

        if ($oldtos != $model->value) {
            User::query()->update(['term_accepted' => false]);
        }

        return Redirect::route('manage.home')->with('last_tab', 'settings')->with('last_action', 'update')->with('status', $result);
    }

    public function ResetRedis(Request $request)
    {
        foreach (Redis::keys("usertask_*") as $key){
            $user_id = explode("usertask_", $key, 2);
            if (count($user_id) > 1) {
                Redis::del("usertask_" . $user_id[1]);
            } else {
                Redis::del("usertask_" . $user_id);
            }
        }
        foreach (Redis::keys("api_*") as $key){
            $user_id = explode("api_", $key, 2);
            if (count($user_id) > 1) {
                Redis::del("api_" . $user_id[1]);
            } else {
                Redis::del("api_" . $user_id);
            }
        }
        
        return Redirect::route('manage.home')->with('last_tab', 'settings')->with('last_action', 'resetRedis')->with('status', 'success');
    }
}
