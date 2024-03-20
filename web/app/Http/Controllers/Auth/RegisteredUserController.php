<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\SystemSetting;
use App\Models\Groups;
use App\Models\User;
use App\Rules\AllowedEmailDomain;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        if (SystemSetting::where('key', 'allowRegister')->where('value', 'true')->exists()) {
            return view('auth.register');
        }
        return redirect()->intended('/');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (SystemSetting::where('key', 'allowRegister')->where('value', 'true')->exists()) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class, new AllowedEmailDomain()],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'invite_token' => ['max:32'],
            ]);
            if (SystemSetting::where('key', 'register_need_invite')->where('value', 'true')->exists()) {
                $request->validate([
                    'invite_token' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            if (!Groups::where('invite_token', '=', $value)->exists()) {
                                $fail("This invite token doesn't exist");
                            }
                        },
                    ],
                ]);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            $defaultGroup = env('DEFAULT_GROUP');
            $inviteToken = $request->invite_token ?? $defaultGroup;
            
            $group = Groups::where('invite_token', '=', $inviteToken)->first();
            
            if (!$group && $request->invite_token && $defaultGroup) {
                $group = Groups::where('invite_token', '=', $defaultGroup)->first();
            }
            
            if ($group) {
                $user->group_id = $group->id;
                $user->save();
            }

            $user->tokens()->delete();
            $user->createToken('API_Token', ['access_api']);

            event(new Registered($user));

            Auth::login($user);

            if (Auth::user()->hasVerifiedEmail()){
                if (Auth::user()->hasPerm('tab_Room')){
                    return redirect()->intended("/room");
                }else{
                    return redirect()->intended("/");
                }
            }
            return redirect()->intended('/verify-email');
        }
        return redirect('/');
    }
}
