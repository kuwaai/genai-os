<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;

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

        $model = SystemSetting::where('key', 'upload_max_size_mb')->first();
        $model->value = strval(intval($request->input('upload_max_size_mb') ?? '0'));
        $model->save();

        $model = SystemSetting::where('key', 'upload_allowed_extensions')->first();
        $upload_allowed_extensions = array_filter(explode(',', $request->input('upload_allowed_extensions') ?? ''));
        $upload_allowed_extensions = array_map(fn($v): string => trim($v), $upload_allowed_extensions);
        $model->value = implode(',', $upload_allowed_extensions);
        $model->save();

        $model = SystemSetting::where('key', 'upload_max_file_count')->first();
        $model->value = strval(intval($request->input('upload_max_file_count') ?? '-1'));
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
        foreach (Redis::keys('usertask_*') as $key) {
            $user_id = explode('usertask_', $key, 2);
            if (count($user_id) > 1) {
                Redis::del('usertask_' . $user_id[1]);
            } else {
                Redis::del('usertask_' . $user_id);
            }
        }
        foreach (Redis::keys('api_*') as $key) {
            $user_id = explode('api_', $key, 2);
            if (count($user_id) > 1) {
                Redis::del('api_' . $user_id[1]);
            } else {
                Redis::del('api_' . $user_id);
            }
        }

        return Redirect::route('manage.home')->with('last_tab', 'settings')->with('last_action', 'resetRedis')->with('status', 'success');
    }

    public function updateWeb(Request $request)
    {
        // Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
    
        try {
            // Ensure the working directory is the root of the Laravel project
            $projectRoot = base_path(); // Laravel root path
            // Determine the script path based on the operating system
            $isWindows = stripos(PHP_OS, 'WIN') === 0;
            $scriptPath = $isWindows ? '/executables/bat/production_update.bat' : '/executables/sh/production_update.sh';
            $scriptDir = dirname($scriptPath); // Get the directory for the script
    
            // Change to the script directory first
            chdir(base_path() . $scriptDir); // Change the working directory
    
            // List of commands to run
            $commands = ['git stash', 'git pull'];
    
            // Run git commands first
            foreach ($commands as $command) {
                $process = Process::fromShellCommandline($command);
                $process->setEnv('PATH', '/usr/local/bin:/usr/bin:/bin');
                $process->setTimeout(null); // No timeout
    
                // Start process
                $process->run(function ($type, $buffer) use ($projectRoot) {
                    // Send error messages if specific output is detected
                    if (strpos($buffer, 'password') !== false) {
                        echo 'data: ' . json_encode(['status' => 'error', 'output' => 'Password prompt detected. Cancelling job...']) . "\n\n";
                        ob_flush();
                        flush();
                        exit(); // Exit to stop further command execution
                    }
    
                    if (strpos($buffer, 'dubious ownership') !== false) {
                        echo 'data: ' . json_encode(['status' => 'error', 'output' => "Dubious ownership detected. Please run: git config --global --add safe.directory {$projectRoot}"]) . "\n\n";
                        ob_flush();
                        flush();
                        exit(); // Exit to stop further command execution
                    }
    
                    // Send output to the client in SSE format
                    echo 'data: ' . json_encode(['status' => 'progress', 'output' => trim($buffer)]) . "\n\n";
                    ob_flush();
                    flush();
                });
    
                // Check for successful command execution
                if (!$process->isSuccessful()) {
                    echo 'data: ' . json_encode(['status' => 'error', 'output' => "Error executing command: $command"]) . "\n\n";
                    ob_flush();
                    flush();
                    exit();
                }
            }
    
            // Make the script executable
            if (!$isWindows) {
                $chmodProcess = Process::fromShellCommandline("chmod +x " . basename($scriptPath));
                $chmodProcess->setEnv('PATH', '/usr/local/bin:/usr/bin:/bin');
                $chmodProcess->run(function ($type, $buffer) {
                    echo 'data: ' . json_encode(['status' => 'progress', 'output' => trim($buffer)]) . "\n\n";
                    ob_flush();
                    flush();
                });
    
                if (!$chmodProcess->isSuccessful()) {
                    echo 'data: ' . json_encode(['status' => 'error', 'output' => 'Error making the script executable.']) . "\n\n";
                    ob_flush();
                    flush();
                    exit();
                }
            }
    
            // After successful git commands and chmod, execute the respective script
            $process = Process::fromShellCommandline('./' . basename($scriptPath));
            $process->setEnv('PATH', '/usr/local/bin:/usr/bin:/bin');
            $process->setTimeout(null); // No timeout
    
            $process->run(function ($type, $buffer) {
                // Send output from the script
                echo 'data: ' . json_encode(['status' => 'progress', 'output' => trim($buffer)]) . "\n\n";
                ob_flush();
                flush();
            });
    
            // Check for successful script execution
            if (!$process->isSuccessful()) {
                echo 'data: ' . json_encode(['status' => 'error', 'output' => 'Error executing the script.']) . "\n\n";
                ob_flush();
                flush();
                exit();
            }
    
            echo 'data: ' . json_encode(['status' => 'success', 'output' => 'Update completed successfully!']) . "\n\n";
            ob_flush();
            flush();
        } catch (\Exception $e) {
            echo 'data: ' . json_encode(['status' => 'error', 'output' => $e->getMessage()]) . "\n\n";
            ob_flush();
            flush();
        }
    }
    
}
