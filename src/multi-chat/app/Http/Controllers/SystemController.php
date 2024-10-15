<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\RedisController;
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
        foreach (['usertask_', 'api_'] as $prefix) {
            foreach (Redis::keys("{$prefix}*") as $key) {
                $cleanKey = RedisController::cleanRedisKey($key, $prefix);
                Redis::del($cleanKey);
            }
        }

        return Redirect::route('manage.home')->with('last_tab', 'settings')->with('last_action', 'resetRedis')->with('status', 'success');
    }
    public function checkUpdate(Request $request)
    {
        try {
            chdir(base_path()); // Change to Laravel root path

            $process = Process::fromShellCommandline('git fetch && git status');

            $gitSshCommand = SystemSetting::where('key', 'updateweb_git_ssh_command')->first()->value ?? '';

            $env = [
                'PATH' => '/usr/local/bin:/usr/bin:/bin',
            ];

            if (!empty($gitSshCommand)) {
                $env['GIT_SSH_COMMAND'] = $gitSshCommand;
            }

            $process->setEnv($env);
            $process->setTimeout(null); // No timeout

            $process->run(function ($type, $buffer) {
                echo 'data: ' . json_encode(['status' => 'progress', 'output' => trim($buffer)]) . "\n\n";
                ob_flush();
                flush();
            });

            if (!$process->isSuccessful()) {
                return response()->json(['status' => 'error', 'output' => 'Error checking for updates.']);
            }

            $output = $process->getOutput();
            $status = strpos($output, 'Your branch is up to date') !== false ? 'up-to-date' : 'update-available';
            $message = $status === 'up-to-date' ? 'Your branch is up to date.' : 'New updates are available.';

            return response()->json(['status' => $status, 'output' => $message]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'output' => $e->getMessage()]);
        }
    }

    public function updateWeb(Request $request)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        try {
            $projectRoot = base_path();
            $scriptPath = stripos(PHP_OS, 'WIN') === 0 ? '/executables/bat/production_update.bat' : '/executables/sh/production_update.sh';
            chdir($projectRoot . dirname($scriptPath)); // Change to script directory

            foreach (['git stash', 'git pull'] as $command) {
                $this->runCommand($command, $projectRoot);
            }

            // Make the script executable if not Windows
            if (stripos(PHP_OS, 'WIN') === false) {
                $this->makeExecutable(basename($scriptPath));
            }

            $this->runCommand('./' . basename($scriptPath));

            echo 'data: ' . json_encode(['status' => 'success', 'output' => 'Update completed successfully!']) . "\n\n";
            ob_flush();
            flush();
        } catch (\Exception $e) {
            echo 'data: ' . json_encode(['status' => 'error', 'output' => $e->getMessage()]) . "\n\n";
            ob_flush();
            flush();
        }
    }

    private function runCommand(string $command, string $projectRoot)
    {
        $gitSshCommand = SystemSetting::where('key', 'updateweb_git_ssh_command')->first()->value ?? '';

        $env = [
            'PATH' => '/usr/local/bin:/usr/bin:/bin',
        ];

        if (!empty($gitSshCommand)) {
            $env['GIT_SSH_COMMAND'] = $gitSshCommand;
        }

        $process = Process::fromShellCommandline($command);
        $process->setEnv($env);
        $process->setTimeout(null); // No timeout

        $process->run(function ($type, $buffer) use ($projectRoot) {
            $this->handleOutput($buffer, $projectRoot);
        });

        if (!$process->isSuccessful()) {
            echo 'data: ' . json_encode(['status' => 'error', 'output' => "Error executing command: $command"]) . "\n\n";
            ob_flush();
            flush();
            exit();
        }
    }

    private function handleOutput(string $buffer, string $projectRoot)
    {
        if (strpos($buffer, 'password') !== false) {
            $this->sendError('Password prompt detected. Cancelling job...');
        } elseif (strpos($buffer, 'dubious ownership') !== false) {
            $this->sendError("Dubious ownership detected. Please run: git config --global --add safe.directory {$projectRoot}");
        } else {
            echo 'data: ' . json_encode(['status' => 'progress', 'output' => trim($buffer)]) . "\n\n";
            ob_flush();
            flush();
        }
    }

    private function makeExecutable(string $scriptName)
    {
        $process = Process::fromShellCommandline("chmod +x $scriptName");
        $process->run(function ($type, $buffer) {
            echo 'data: ' . json_encode(['status' => 'progress', 'output' => trim($buffer)]) . "\n\n";
            ob_flush();
            flush();
        });

        if (!$process->isSuccessful()) {
            echo 'data: ' . json_encode(['status' => 'error', 'output' => 'Error making the script executable.']) . "\n\n";
            ob_flush();
            flush();
            exit();
        }
    }

    private function sendError(string $message)
    {
        echo 'data: ' . json_encode(['status' => 'error', 'output' => $message]) . "\n\n";
        ob_flush();
        flush();
        exit();
    }
}
