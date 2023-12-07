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

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        if (
            SystemSetting::where('key', 'allowRegister')
                ->where('value', 'true')
                ->exists()
        ) {
            return view('auth.register');
        }
        return Redirect::route('/');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (
            SystemSetting::where('key', 'allowRegister')
                ->where('value', 'true')
                ->exists()
        ) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'invite_token' => ['max:32'],
            ]);

            if (
                SystemSetting::where('key', 'register_need_invite')
                    ->where('value', 'true')
                    ->exists()
            ) {
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
            if ($request->invite_token) {
                $group = Groups::where('invite_token', '=', $request->invite_token);
                if ($group->exists()) {
                    $user->group_id = $group->first()->id;
                    $user->save();
                }
            }

            $user->tokens()->delete();
            $user->createToken('API_Token', ['access_api']);

            event(new Registered($user));

            Auth::login($user);

            return redirect("/");
        }
        return redirect("/");
    }
}
