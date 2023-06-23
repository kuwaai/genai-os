<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        if (
            $request
                ->user()
                ->tokens()
                ->where('name', 'API_Token')
                ->count() != 1
        ) {
            $request
                ->user()
                ->tokens()
                ->where('name', 'API_Token')
                ->delete();
            $request->user()->createToken('API_Token', ['access_api']);
        }

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        if (!$request->user()->forDemo) {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();
            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }
        return Redirect::route('profile.edit')->with('status', 'failed-demo-acc');
    }

    public function chatgpt_update(Request $request)
    {
        if (!$request->user()->forDemo) {
            $request->user()->fill(["openai_token"=> $request->input('openai_token')])->save();
            return Redirect::route('profile.edit')->with('status', 'chatgpt-token-updated');
        }
        return Redirect::route('profile.edit')->with('status', 'failed-demo-acc');
    }

    /**
     * Renew the user's API Token.
     */
    public function renew(Request $request): RedirectResponse
    {
        if (!$request->user()->forDemo) {
            $request
                ->user()
                ->tokens()
                ->where('name', 'API_Token')
                ->delete();
            $request->user()->createToken('API_Token', ['access_api']);

            return Redirect::route('profile.edit')->with('status', 'apiToken-updated');
        }
        return Redirect::route('profile.edit')->with('status', 'failed-demo-acc');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (!$request->user()->forDemo) {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current-password'],
            ]);

            $user = $request->user();

            Auth::logout();

            $user->tokens()->delete();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');
        }
        return Redirect::route('profile.edit')->with('status', 'failed-demo-acc');
    }
}
