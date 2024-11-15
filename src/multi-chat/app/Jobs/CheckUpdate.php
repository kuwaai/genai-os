<?php

namespace App\Jobs;

use App\Models\SystemSetting;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use App\Jobs\RequestChat;
use Illuminate\Support\Facades\File;
use App\Models\LLMs;
use Illuminate\Support\Collection;

class CheckUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ignore;

    public function __construct($ignore = false)
    {
        $this->ignore = $ignore;
    }

    public function handle()
    {
        try {
            $checkUpdateScript = base_path('app/Console/check-update.php');

            $env = [
                'PATH' => SystemSetting::where('key', 'updateweb_path')->value('value') ?: getenv('PATH'),
                'GIT_SSH_COMMAND' => SystemSetting::where('key', 'updateweb_git_ssh_command')->value('value') ?? '',
            ];

            if (File::exists($checkUpdateScript)) {
                $process = Process::fromShellCommandline('php ' . $checkUpdateScript)->setEnv($env)->setTimeout(null);
                $process->setTimeout(null);
                $process->run();

                if (!$process->isSuccessful()) {
                    $errorMessage = $process->getErrorOutput();
                    $errorMessage = $this->parseMessage($errorMessage);
                    SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                    return;
                }

                $output = $process->getOutput();
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $output]);
                return;
            }

            chdir(base_path());
            $env = [
                'PATH' => SystemSetting::where('key', 'updateweb_path')->value('value') ?: getenv('PATH'),
                'GIT_SSH_COMMAND' => SystemSetting::where('key', 'updateweb_git_ssh_command')->value('value') ?? '',
            ];

            $updateProcess = Process::fromShellCommandline('git remote update')->setEnv($env)->setTimeout(null);
            $updateProcess->run();

            if (!$updateProcess->isSuccessful()) {
                $errorMessage = $updateProcess->getErrorOutput();
                $errorMessage = $this->parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return;
            }

            $localCommitProcess = Process::fromShellCommandline('git rev-parse @')->setEnv($env)->setTimeout(null);
            $localCommitProcess->run();

            if (!$localCommitProcess->isSuccessful()) {
                $errorMessage = $localCommitProcess->getErrorOutput();
                $errorMessage = $this->parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return;
            }
            $localCommit = trim($localCommitProcess->getOutput());

            $upstreamCommitProcess = Process::fromShellCommandline('git rev-parse @{u}')->setEnv($env)->setTimeout(null);
            $upstreamCommitProcess->run();

            if (!$upstreamCommitProcess->isSuccessful()) {
                $errorMessage = $upstreamCommitProcess->getErrorOutput();
                $errorMessage = $this->parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return;
            }
            $upstreamCommit = trim($upstreamCommitProcess->getOutput());

            $baseCommitProcess = Process::fromShellCommandline('git merge-base @ @{u}')->setEnv($env)->setTimeout(null);
            $baseCommitProcess->run();

            if (!$baseCommitProcess->isSuccessful()) {
                $errorMessage = $baseCommitProcess->getErrorOutput();
                $errorMessage = $this->parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return;
            }
            $baseCommit = trim($baseCommitProcess->getOutput());

            if ($localCommit === $upstreamCommit) {
                $status = 'no-update';
            } elseif ($localCommit === $baseCommit) {
                $status = 'update-available';
            } elseif ($upstreamCommit === $baseCommit) {
                $status = 'no-update';
            } else {
                $status = 'update-available';
            }
            SystemSetting::where('key', 'cache_update_check')->update(['value' => $status]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorMessage = $this->parseMessage($errorMessage);
            SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
        }

        return;
    }

    private function parseMessage($buffer)
    {
        $encoding = mb_detect_encoding($buffer, ['UTF-8', 'BIG5', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding !== false && $encoding !== 'UTF-8') {
            $buffer = mb_convert_encoding($buffer, 'UTF-8', $encoding);
        }

        return $buffer;
    }
}
