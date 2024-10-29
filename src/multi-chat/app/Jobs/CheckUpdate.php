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

class CheckUpdate implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ignore;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'check_update_job';
    }

    public function __construct($ignore = false)
    {
        $this->ignore = $ignore;
    }
    
    public function handle()
    {
        try {
            // Define the path to the check-update.php script
            $checkUpdateScript = base_path('app/Console/check-update.php');
    
            // Check if check-update.php exists
            if (File::exists($checkUpdateScript)) {
                // Run the PHP script as an external process
                $process = new Process(['php', $checkUpdateScript]);
                $process->setTimeout(null);
                $process->run();
    
                // Check if the process was successful
                if (!$process->isSuccessful()) {
                    // Capture and save any error output from the process
                    $errorMessage = $process->getErrorOutput();
                    $errorMessage = $this->parseMessage($errorMessage);
                    SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                    return;
                }
    
                // Capture and save the output from the process
                $output = $process->getOutput();
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $output]);
                return;
            }
    
            // Proceed with the existing git operations if check-update.php does not exist
            chdir(base_path());
            $env = [
                'PATH' => SystemSetting::where('key', 'updateweb_path')->value('value') ?: getenv('PATH'),
                'GIT_SSH_COMMAND' => SystemSetting::where('key', 'updateweb_git_ssh_command')->value('value') ?? '',
            ];
    
            $fetchProcess = Process::fromShellCommandline('git fetch --all')->setEnv($env)->setTimeout(null);
            $fetchProcess->run();
            if (!$fetchProcess->isSuccessful()) {
                $errorMessage = $fetchProcess->getErrorOutput();
                $errorMessage = $this->parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return;
            }
    
            $statusProcess = Process::fromShellCommandline('git status')->setEnv($env)->setTimeout(null);
            $statusProcess->run();
            if (!$statusProcess->isSuccessful()) {
                $errorMessage = $statusProcess->getErrorOutput();
                $errorMessage = $this->parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return;
            }
    
            $output = $statusProcess->getOutput();
            $isAhead = strpos($output, 'Your branch is ahead of') !== false;
            $isBehind = strpos($output, 'Your branch is behind') !== false;
    
            $status = $isAhead && $isBehind 
                ? 'both-ahead-behind' 
                : ($isAhead 
                    ? 'no-update' 
                    : ($isBehind 
                        ? 'update-available' 
                        : (strpos($output, 'Your branch is up to date') !== false 
                            ? 'up-to-date' 
                            : (strpos($output, 'Changes not staged for commit') !== false || strpos($output, 'Untracked files') !== false 
                                ? 'no-update' 
                                : 'Unknown'))));
    
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
