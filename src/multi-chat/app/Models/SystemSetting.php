<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class SystemSetting extends Model
{
    use HasFactory;
    protected $table = 'system_setting';
    protected $fillable = ['key', 'value'];
    static function checkUpdate($ignore = false)
    {
        if (!$ignore) {
            $updatedAt = SystemSetting::where('key', 'cache_update_check')->value('updated_at');
            if ($updatedAt && $updatedAt >= now()->subMinutes(5)) {
                return SystemSetting::where('key', 'cache_update_check')->value('value');
            }
        }

        try {
            chdir(base_path());
            $env = [
                'PATH' => SystemSetting::where('key', 'updateweb_path')->value('value') ?: getenv('PATH'),
                'GIT_SSH_COMMAND' => SystemSetting::where('key', 'updateweb_git_ssh_command')->value('value') ?? '',
            ];

            $fetchProcess = Process::fromShellCommandline('git fetch --all')->setEnv($env)->setTimeout(null);
            $fetchProcess->run();
            if (!$fetchProcess->isSuccessful()) {
                $errorMessage = $fetchProcess->getErrorOutput();
                $errorMessage = self::parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return $errorMessage;
            }

            $statusProcess = Process::fromShellCommandline('git status')->setEnv($env)->setTimeout(null);
            $statusProcess->run();
            if (!$statusProcess->isSuccessful()) {
                $errorMessage = $statusProcess->getErrorOutput();
                $errorMessage = self::parseMessage($errorMessage);
                SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
                return $errorMessage;
            }

            $output = $statusProcess->getOutput();
            $isAhead = strpos($output, 'Your branch is ahead of') !== false;
            $isBehind = strpos($output, 'Your branch is behind') !== false;

            $status = $isAhead && $isBehind ? 'both-ahead-behind' : ($isAhead ? 'no-update' : ($isBehind ? 'update-available' : (strpos($output, 'Your branch is up to date') !== false ? 'up-to-date' : (strpos($output, 'Changes not staged for commit') !== false || strpos($output, 'Untracked files') !== false ? 'no-update' : 'unknown'))));

            SystemSetting::where('key', 'cache_update_check')->update(['value' => $status]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorMessage = self::parseMessage($errorMessage);
            SystemSetting::where('key', 'cache_update_check')->update(['value' => $errorMessage]);
            return $errorMessage;
        }

        return $status;
    }

    static function parseMessage($buffer)
    {
        $encoding = mb_detect_encoding($buffer, ['UTF-8', 'BIG5', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding !== false && $encoding !== 'UTF-8') {
            $buffer = mb_convert_encoding($buffer, 'UTF-8', $encoding);
        }

        return $buffer;
    }
}
