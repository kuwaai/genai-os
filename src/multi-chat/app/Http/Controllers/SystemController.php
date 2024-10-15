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
    public static function updateSystemSetting($key, $value)
    {
        SystemSetting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
    }

    public function update(Request $request): RedirectResponse
    {
        $extractBaseUrl = fn($url) => $url ? parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . (parse_url($url, PHP_URL_PORT) ? ':' . parse_url($url, PHP_URL_PORT) : '') : '';

        if ($request->input('tab') === 'kernel') {
            foreach (
                [
                    'kernel_location' => $extractBaseUrl($request->input('kernel_location') ?? 'http://localhost:9000'),
                    'safety_guard_location' => $extractBaseUrl($request->input('safety_guard_location')),
                ]
                as $key => $location
            ) {
                $this->updateSystemSetting($key, $location);
            }
            $this->updateSystemSetting('updateweb_git_ssh_command', $request->input('updateweb_git_ssh_command'));
            $result = 'success';
        } elseif ($request->input('tab') === 'settings') {
            $smtpConfigured = !in_array(null, [config('app.MAIL_MAILER'), config('app.MAIL_HOST'), config('app.MAIL_PORT'), config('app.MAIL_USERNAME'), config('app.MAIL_PASSWORD'), config('app.MAIL_ENCRYPTION'), config('app.MAIL_FROM_ADDRESS'), config('app.MAIL_FROM_NAME')]);

            foreach (['allow_register', 'register_need_invite'] as $key) {
                $this->updateSystemSetting($key, $request->input($key) === 'allow' && $smtpConfigured ? 'true' : 'false');
            }

            $result = $smtpConfigured ? 'success' : 'smtp_not_configured';

            // Update announcement
            $announcement = $request->input('announcement');
            $currentAnnouncement = SystemSetting::where('key', 'announcement')->value('value');
            if ($currentAnnouncement !== $announcement) {
                $this->updateSystemSetting('announcement', $announcement);
                User::query()->update(['announced' => false]);
            }

            $this->updateSystemSetting('warning_footer', $request->input('warning_footer'));

            foreach (['upload_max_size_mb', 'upload_max_file_count'] as $key) {
                $value = ((string) intval($request->input($key))) ?? '10';
                if ($value === '0' && $request->input($key) !== '0') {
                    $value = '10';
                }
                $this->updateSystemSetting($key, $value);
            }

            $uploadExtensions = $request->input('upload_allowed_extensions') ? implode(',', array_map('trim', explode(',', $request->input('upload_allowed_extensions')))) : 'pdf,doc,docx,odt,ppt,pptx,odp,xlsx,xls,ods,eml,txt,md,csv,json,jpeg,jpg,gif,png,avif,webp,bmp,ico,cur,tiff,tif,zip,mp3,wav,mp4';
            $this->updateSystemSetting('upload_allowed_extensions', $uploadExtensions);
            $tos = $request->input('tos');
            $currentTos = SystemSetting::where('key', 'tos')->value('value');
            if ($currentTos !== $tos) {
                $this->updateSystemSetting('tos', $tos);
                User::query()->update(['term_accepted' => false]);
            }
        }

        return Redirect::route('manage.home')->with(['last_tab' => $request->input('tab'), 'last_action' => 'update', 'status' => $result]);
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
