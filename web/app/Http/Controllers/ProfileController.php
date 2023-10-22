<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Jobs\RequestChat;
use GuzzleHttp\Client;
use App\Models\LLMs;
use App\Models\User;
use DB;

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
        $status = [];
        if ($request->validated()) {
            if ($request->validated()['name']) {
                if ($request->user()->hasPerm('Profile_update_name')) {
                    $request->user()->name = $request->validated()['name'];
                } else {
                    return Redirect::route('profile.edit')->with('status', 'no-changes');
                }
            }
            if ($request->validated()['email']) {
                if ($request->user()->hasPerm('Profile_update_email')) {
                    $request->user()->email = $request->validated()['email'];
                } else {
                    return Redirect::route('profile.edit')->with('status', 'no-changes');
                }
            }

            if ($request->user()->isDirty('email') || $request->user()->isDirty('name')) {
                if ($request->user()->isDirty('email')) {
                    $request->user()->email_verified_at = null;
                }
                $request->user()->save();
                return Redirect::route('profile.edit')->with('status', 'profile-updated');
            }
        }

        return Redirect::route('profile.edit');
    }

    public function chatgpt_update(Request $request)
    {
        if (!$request->user()->forDemo) {
            $request
                ->user()
                ->fill(['openai_token' => $request->input('openai_token')])
                ->save();
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

    public function api_auth(Request $request)
    {
        $jsonData = $request->json()->all();
        $result = DB::table('personal_access_tokens')
            ->join('users', 'tokenable_id', '=', 'users.id')
            ->select('tokenable_id', 'users.id', 'users.name', 'openai_token')
            ->where('token', str_replace('Bearer ', '', $request->header('Authorization')));
        if ($result->exists()) {
            $user = $result->first();
            if (User::find($user->id)->hasPerm('Chat_read_access_to_api')) {
                $response = [
                    'status' => 'success',
                    'message' => 'Authentication successful',
                    'tokenable_id' => $user->tokenable_id,
                    'name' => $user->name,
                ];
                if (isset($jsonData['messages']) && isset($jsonData['model'])) {
                    $llm = LLMs::where('access_code', '=', $jsonData['model']);

                    if ($llm->exists()) {
                        $llm = $llm->first();

                        $tmp = json_encode($jsonData['messages']);

                        if ($tmp === false && json_last_error() !== JSON_ERROR_NONE) {
                            $errorResponse = [
                                'status' => 'error',
                                'message' => 'The msg format is incorrect.',
                            ];
                            return response()->json($errorResponse);
                        } else {
                            // Input is a valid JSON string
                            $response['output'] = '';
                            $client = new Client(['timeout' => 300]);

                            RequestChat::dispatch($tmp, $llm->access_code, $user->id, -$user->id, $user->openai_token, 'aielection_' . $user->id);

                            $req = $client->get(route('api.stream'), [
                                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                                'query' => [
                                    'key' => config('app.API_Key'),
                                    'channel' => 'aielection_' . $user->id,
                                ],
                                'stream' => true,
                            ]);

                            $req = $req->getBody()->getContents();
                            $response['output'] = explode('[ENDEDPLACEHOLDERUWU]', $req)[0];
                        }
                    } else {
                        // Handle the case where the specified model doesn't exist
                        $errorResponse = [
                            'status' => 'error',
                            'message' => 'The specified model does not exist.',
                        ];
                        return response()->json($errorResponse);
                    }
                } else {
                    // Handle the case where 'messages' and 'model' are not present in $jsonData
                    $errorResponse = [
                        'status' => 'error',
                        'message' => 'The JSON data is missing required fields.',
                    ];
                    return response()->json($errorResponse);
                }

                return response()->json($response);
            } else {
                $errorResponse = [
                    'status' => 'error',
                    'message' => 'You have no permission to use Chat API',
                ];
            }
        } else {
            $errorResponse = [
                'status' => 'error',
                'message' => 'Authentication failed',
            ];
        }

        return response()->json($errorResponse);
    }

    public function api_stream(Request $request)
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('charset', 'utf-8');
        $response->headers->set('Connection', 'close');

        $response->setCallback(function () use ($response, $request) {
            if (config('app.API_Key') != null && config('app.API_Key') == $request->input('key')) {
                $channel = $request->input('channel');
                if ($channel != null) {
                    if (strpos($channel, 'aielection_') === 0) {
                        $client = Redis::connection();
                        $client->subscribe($channel, function ($message, $raw_history_id) use ($client) {
                            global $result;
                            [$type, $msg] = explode(' ', $message, 2);
                            if ($type == 'Ended') {
                                echo $result . '[ENDEDPLACEHOLDERUWU]';
                                ob_flush();
                                flush();
                                $client->disconnect();
                            } elseif ($type == 'New') {
                                $result = json_decode($msg)->msg;
                                ob_flush();
                                flush();
                            }
                        });
                    }
                }
            }
        });

        return $response;
    }
}
