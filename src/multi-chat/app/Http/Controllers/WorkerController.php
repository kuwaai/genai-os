<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class WorkerController extends Controller
{
    // Route handler for starting workers
    public function start(Request $request)
    {
        $count = $request->validate(['count' => 'required|integer|min:1'])['count'];
        return $this->startWorkers($count);
    }

    // Route handler for stopping workers
    public function stop()
    {
        return $this->stopWorkers();
    }

    // Function to start a specified number of workers
    public function startWorkers(int $count = 10)
    {
        $artisanPath = base_path('artisan');
        $logFileBase = base_path('storage/logs/worker.log');

        for ($i = 0; $i < $count; $i++) {
            $logFile = $this->generateLogFileName($logFileBase);
            $command = PHP_OS_FAMILY === 'Windows' ? "start /B php \"{$artisanPath}\" queue:work >> \"{$logFile}\"" : "php {$artisanPath} queue:work >> {$logFile} 2>&1 &";

            $env = [
                'PATH' => SystemSetting::where('key', 'updateweb_path')->value('value') ?: getenv('PATH'),
                'GIT_SSH_COMMAND' => SystemSetting::where('key', 'updateweb_git_ssh_command')->value('value') ?? '',
            ];
            try {
                $worker = Process::fromShellCommandline($command)->setEnv($env)->setTimeout(null);
                $worker->run();
                while (!file_exists($logFile)) {
                    usleep(100000);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => __('workers.label.worker_start_failed') . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => __('workers.label.worker_started')]);
    }

    // Function to stop all workers and merge log files
    public function stopWorkers()
    {
        Artisan::call('queue:restart');

        for ($elapsedTime = 0; $elapsedTime < 10 && $this->get()->getData()->worker_count > 0; $elapsedTime++) {
            usleep(500000);
        }

        $logDirectory = base_path('storage/logs/');
        $mergedLogFile = $logDirectory . 'workers.log';

        try {
            $logFiles = glob($logDirectory . 'worker.log.*');

            if (empty($logFiles)) {
                return response()->json(['message' => __('workers.label.no_workers')]);
            }

            $mergedFileHandle = fopen($mergedLogFile, 'a') ?: touch($mergedLogFile);

            foreach ($logFiles as $logFile) {
                if (filesize($logFile) > 0) {
                    fwrite($mergedFileHandle, file_get_contents($logFile));
                }
                unlink($logFile);
            }

            fclose($mergedFileHandle);
        } catch (\Exception $e) {
            \Log::error('Log merge error: ' . $e->getMessage());
        }

        return response()->json(['message' => __('workers.label.worker_stopped')]);
    }

    // Function to generate a unique log file name
    private function generateLogFileName($baseName)
    {
        $i = 0;
        while (file_exists($baseName . '.' . $i)) {
            $i++;
        }
        return $baseName . '.' . $i;
    }
    // Get the number of active worker processes for a specific artisan command
    public static function getWorkerCount($command = 'queue:work')
    {
        $projectRoot = base_path(); // Get the root of the project
        $artisanFile = $projectRoot . '/artisan'; // Path to the artisan file
        $count = 0; // Initialize the count of worker processes

        if (PHP_OS_FAMILY === 'Windows') {
            // Attempt to use wmic to get the command line of running PHP processes
            $cmd = 'wmic process where "name=\'php.exe\'" get CommandLine, ProcessId';
            $processes = shell_exec($cmd);

            if (!empty($processes)) {
                $lines = array_filter(explode("\n", trim($processes)));
            } else {
                // Fallback to PowerShell if wmic fails
                $cmd = 'powershell -command "Get-CimInstance Win32_Process -Filter \"Name=\'php.exe\'\" | Select-Object ProcessId, CommandLine"';
                $processes = shell_exec($cmd);
                $lines = array_filter(explode("\n", trim($processes)));
            }
            foreach ($lines as $line) {
                if (strpos($line, 'php') !== false) {
                    // Check if line contains 'php'
                    if (preg_match('/php\s+"([^"]+)"\s+' . preg_quote($command, '/') . '/', $line, $matches)) {
                        if (isset($matches[1]) && realpath($matches[1]) === realpath($artisanFile)) {
                            $count++;
                        }
                    }
                }
            }
        } else {
            // For non-Windows systems
            $cmd = "ps aux | grep 'php' | grep '$artisanFile' | grep '$command' | grep -v grep";
            $processes = shell_exec($cmd);
            $count = count(array_filter(explode("\n", trim($processes))));
        }

        return $count;
    }

    // Main method to return worker count as a JSON response
    public function get()
    {
        return response()->json(['worker_count' => $this->getWorkerCount('queue:work')]);
    }

    public static function cleanRedisKey($key, $pattern)
    {
        return strpos($key, $pattern) !== false ? substr($key, strpos($key, $pattern)) : $key;
    }
}
