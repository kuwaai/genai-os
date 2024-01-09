<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\APIHistories;
use Illuminate\View\View;
use App\Jobs\RequestChat;
use GuzzleHttp\Client;
use App\Models\LLMs;
use App\Models\User;
use App\Models\Groups;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use DB;
use Crypt;
use Net_IPv4;
use Net_IPv6;

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

    public function api_register(Request $request)
    {
        $lockoutCount = RateLimiter::attempts($request->ip());
        $seconds = $this->ensureIsNotRateLimited($request->ip());
        if ($seconds) {
            return response()->json(['error' => 'Too many attempts, Action freezed until ' . floor($seconds / 60) . ':' . $seconds % 60], 400, [], JSON_UNESCAPED_UNICODE);
        }
        $encryptedData = $request->input('data');
        $path = 'keys/' . $encryptedData[0];
        if ($encryptedData && $encryptedData[0] && Storage::exists($path)) {
            $allowlist = $path . '/allowlist.txt';
            $allowlist = Storage::exists($allowlist) ? explode("\n", Storage::get($allowlist)) : null;

            function isIPInCIDRList($ipAddress, $cidrList)
            {
                $ipVersion = filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 6 : null);

                // Filter CIDR list based on IP version
                $filteredCIDRList = array_filter($cidrList, function ($cidr) use ($ipVersion) {
                    return strpos($cidr, '/') !== false && filter_var(explode('/', $cidr)[0], FILTER_VALIDATE_IP, $ipVersion === 4 ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV6) !== false;
                });

                foreach ($filteredCIDRList as $cidr) {
                    [$subnet, $mask] = explode('/', $cidr);
                    $mask = (int) $mask;

                    if ($ipVersion === 4) {
                        $subnetLong = ip2long($subnet);
                        $ipLong = ip2long($ipAddress);
                        $network = $subnetLong & (-1 << 32 - $mask);
                        $ipNetwork = $ipLong & (-1 << 32 - $mask);

                        if ($network === $ipNetwork) {
                            return true;
                        }
                    } elseif ($ipVersion === 6) {
                        $subnetBinary = inet_pton($subnet);
                        $ipBinary = inet_pton($ipAddress);
                        $subnetChunks = unpack('n*', $subnetBinary);
                        $ipChunks = unpack('n*', $ipBinary);

                        for ($i = 1; $i <= $mask / 16; $i++) {
                            $networkChunk = $subnetChunks[$i] & (-1 << 16 - min($mask - ($i - 1) * 16, 16));
                            $ipNetworkChunk = $ipChunks[$i] & (-1 << 16 - min($mask - ($i - 1) * 16, 16));

                            if ($networkChunk !== $ipNetworkChunk) {
                                return false;
                            }
                        }

                        return true;
                    }
                }

                return false;
            }
            if ($allowlist ? isIPInCIDRList($request->ip(), $allowlist) : true) {
                if (isset($encryptedData[3])) {
                    $privKey = Storage::get($path . '/priv.pem');
                    $pubKey = Storage::get($path . '/pub.pem');
                    $invite_code = Storage::get($path . '/invite_code.txt');
                    if ($privKey && $pubKey) {
                        function decrypt($data, $privKey, $pubKey)
                        {
                            $result = openssl_private_decrypt(base64_decode($data), $decryptedData, $privKey);
                            if ($result) {
                                // Attempt verification using the corresponding public key
                                $result = openssl_public_decrypt($decryptedData, $data, $pubKey);
                                if ($result) {
                                    return $data;
                                }
                            }
                            throw new \Illuminate\Contracts\Encryption\DecryptException();
                        }
                        try {
                            // Attempt decryption using the current private key
                            $accData = (object) ['name' => decrypt($encryptedData[1], $privKey, $pubKey), 'email' => decrypt($encryptedData[2], $privKey, $pubKey), 'password' => decrypt($encryptedData[3], $privKey, $pubKey)];
                            RateLimiter::clear(Str::transliterate($request->ip()));
                            $validator = Validator::make((array) $accData, [
                                'email' => 'bail|required|string|email|max:240|unique:users',
                                'name' => 'required|string|max:240',
                                'password' => [
                                    'required',
                                    'string',
                                    function ($attribute, $value, $fail) {
                                        if (!preg_match('/^\$2a\$10\$/', $value) && !preg_match('/^\$2y\$10\$/', $value)) {
                                            $fail('The password must be hashed using bcrypt.');
                                        }
                                    },
                                ],
                            ]);
                            if ($validator->fails()) {
                                return response()->json(['errors' => $validator->errors()], 422, [], JSON_UNESCAPED_UNICODE);
                            }

                            if ($invite_code) {
                                $group_id = Groups::where('invite_token', '=', trim($invite_code))->first()->id ?? null;
                            } else {
                                $group_id = null;
                            }

                            $user = new User();
                            $user->name = $accData->name;
                            $user->email = $accData->email;
                            $user->password = $accData->password;
                            if ($group_id) {
                                $user->group_id = $group_id;
                            }
                            $user->detail = json_encode(['Origin' => basename($encryptedData[0])]);

                            $user->save();
                            $user->markEmailAsVerified();

                            return response()->json(['message' => __('User created successfully')], 201, [], JSON_UNESCAPED_UNICODE);
                        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        }
                    }
                }
            }
        }
        RateLimiter::hit(Str::transliterate($request->ip()), 60 * 60);
        return response()->json(['error' => 'No permission to access this route.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    public function ensureIsNotRateLimited($ip)
    {
        $ip = Str::transliterate($ip);
        if (!RateLimiter::tooManyAttempts($ip, 5)) {
            return false;
        }

        $seconds = RateLimiter::availableIn($ip);

        return $seconds;
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
                            return response()->json($errorResponse, 400, [], JSON_UNESCAPED_UNICODE);
                        } else {
                            // Input is a valid JSON string
                            $history = new APIHistories();
                            $history->fill(['input' => $tmp, 'output' => '* ...thinking... *', 'user_id' => $user->id]);
                            $history->save();

                            $response = new StreamedResponse();
                            $response->headers->set('Content-Type', 'event-stream');
                            $response->headers->set('Cache-Control', 'no-cache');
                            $response->headers->set('X-Accel-Buffering', 'no');
                            $response->headers->set('charset', 'utf-8');
                            $response->headers->set('Connection', 'close');

                            $response->setCallback(function () use ($request, $history, $tmp, $llm, $user) {
                                $client = new Client(['timeout' => 300]);
                                Redis::rpush('api_' . $user->tokenable_id, $history->id);
                                RequestChat::dispatch($tmp, $llm->access_code, $user->id, $history->id, $user->openai_token, 'api_' . $history->id);

                                $req = $client->get(route('api.stream'), [
                                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                                    'query' => [
                                        'key' => config('app.API_Key'),
                                        'user_id' => $user->tokenable_id,
                                        'history_id' => $history->id,
                                    ],
                                    'stream' => true,
                                ]);
                                $stream = $req->getBody();
                                $resp = [
                                    'choices' => [
                                        [
                                            'delta' => [
                                                'content' => '',
                                                'role' => null,
                                            ],
                                        ],
                                    ],
                                    'created' => time(),
                                    'id' => 'chatcmpl-' . $history->id,
                                    'model' => $llm->access_code,
                                    'object' => 'chat.completion',
                                    'usage' => [],
                                ];
                                $line = '';
                                while (!$stream->eof()) {
                                    $char = $stream->read(1);
                                
                                    if ($char === "\n") {
                                        $line = trim($line);
                                        if (substr($line, 0, 5) === 'data:') {
                                            $jsonData = (object)json_decode(trim(substr($line, 5)));
                                            if ($jsonData !== null) {
                                                $resp['choices'][0]['delta']['content'] = $jsonData->msg;
                                                echo 'data: ' . json_encode($resp) . "\n";
                                            }
                                        } elseif (substr($line, 0, 6) === 'event:') {
                                            if (trim(substr($line, 5)) == 'end') {
                                                echo "event: end\n\n";
                                                $client->disconnect();
                                                break;
                                            }
                                        }
                                        $line = "";
                                    } else {
                                        $line .= $char;
                                    }
                                }
                                $history->fill(['output' => $resp['choices'][0]['delta']['content']]);
                                $history->save();
                            });
                            return $response;
                        }
                    } else {
                        // Handle the case where the specified model doesn't exist
                        $errorResponse = [
                            'status' => 'error',
                            'message' => 'The specified model does not exist.',
                        ];
                        return response()->json($errorResponse, 404, [], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    // Handle the case where 'messages' and 'model' are not present in $jsonData
                    $errorResponse = [
                        'status' => 'error',
                        'message' => 'The JSON data is missing required fields.',
                    ];
                    return response()->json($errorResponse, 400, [], JSON_UNESCAPED_UNICODE);
                }
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

        return response()->json($errorResponse, 401, [], JSON_UNESCAPED_UNICODE);
    }

    public function api_abort(Request $request)
    {
        $jsonData = $request->json()->all();
        $result = DB::table('personal_access_tokens')
            ->join('users', 'tokenable_id', '=', 'users.id')
            ->select('tokenable_id', 'users.id', 'users.name', 'openai_token')
            ->where('token', str_replace('Bearer ', '', $request->header('Authorization')));
        if ($result->exists()) {
            $user = $result->first();
            if (User::find($user->id)->hasPerm('Chat_read_access_to_api')) {
                $list = Histories::whereIn('id', \Illuminate\Support\Facades\Redis::lrange('api_' . $user->tokenable_id, 0, -1))
                    ->pluck('id')
                    ->toArray();
                $client = new Client(['timeout' => 300]);
                $agent_location = \App\Models\SystemSetting::where('key', 'agent_location')->first()->value;
                $msg = $client->post($agent_location . RequestChat::$agent_version . '/chat/abort', [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => [
                        'history_id' => json_encode($list),
                        'user_id' => $user->tokenable_id,
                    ],
                ]);
                $response = [
                    'status' => 'success',
                    'message' => 'Aborted',
                    'tokenable_id' => $user->tokenable_id,
                    'name' => $user->name,
                ];

                return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
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

        return response()->json($errorResponse, 401, [], JSON_UNESCAPED_UNICODE);
    }

    public function api_stream(Request $request)
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('charset', 'utf-8');
        $response->headers->set('Connection', 'close');

        $response->setCallback(function () use ($request) {
            if (config('app.API_Key') != null && config('app.API_Key') == $request->input('key')) {
                if ($request->input('history_id') && $request->input('user_id')) {
                    if (in_array($request->input('history_id'), Redis::lrange('api_' . $request->input('user_id'), 0, -1))) {
                        $client = Redis::connection();
                        $client->subscribe('api_' . $request->input('history_id'), function ($message, $raw_history_id) use ($client) {
                            [$type, $msg] = explode(' ', $message, 2);
                            if ($type == 'Ended') {
                                echo 'event: end\n\n';
                                ob_flush();
                                flush();
                                $client->disconnect();
                            } elseif ($type == 'New') {
                                echo 'data: ' . $msg . "\n";
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
