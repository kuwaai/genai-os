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
    // Method to kill workers by their PIDs
    public static function killWorkerPIDs(array $pids)
    {
        if (empty($pids)) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // Kill the processes on Windows using taskkill
            foreach ($pids as $pid) {
                // Windows taskkill command to terminate the process by PID
                shell_exec("taskkill /F /PID {$pid}");
            }
        } else {
            // Kill the processes on Linux/Mac using kill
            foreach ($pids as $pid) {
                // Send SIGTERM signal to the process
                shell_exec("kill -9 {$pid}");
            }
        }
    }
    public static function startWorkersWithCommand(string $commandName = 'queue:work', int $count = 10, ?string $logFileBase = null)
    {
        $artisanPath = base_path('artisan');
        $logFileBase = $logFileBase ?? base_path('storage/logs/worker.log');

        for ($i = 0; $i < $count; $i++) {
            $logFile = self::generateLogFileName($logFileBase);
            $command = PHP_OS_FAMILY === 'Windows' ? "start /B php \"{$artisanPath}\" {$commandName} >> \"{$logFile}\"" : "php {$artisanPath} {$commandName} >> {$logFile} 2>&1 &";

            $env = [
                'PATH' => SystemSetting::where('key', 'updateweb_path')->value('value') ?: getenv('PATH'),
                'GIT_SSH_COMMAND' => SystemSetting::where('key', 'updateweb_git_ssh_command')->value('value') ?? '',
            ];

            try {
                $worker = Process::fromShellCommandline($command)->setEnv($env)->setTimeout(null);
                $worker->run();

                // Wait until the log file is created
                while (!file_exists($logFile)) {
                    usleep(100000);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => __('workers.label.worker_start_failed') . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => __('workers.label.worker_started')]);
    }
    public function startWorkers(int $count = 10)
    {
        $scheduler_workers = WorkerController::getWorkerCount('schedule:work');
        if ($scheduler_workers['count'] == 0) {
            WorkerController::startWorkersWithCommand('schedule:work', 1, base_path('storage/logs/scheduler.log'));
        } elseif ($scheduler_workers['count'] > 1) {
            WorkerController::killWorkerPIDs($scheduler_workers['pids']);
            $this->mergeLogFiles('scheduler.log');
            WorkerController::startWorkersWithCommand('schedule:work', 1, base_path('storage/logs/scheduler.log'));
        }

        return $this::startWorkersWithCommand('queue:work', $count);
    }
    private function mergeLogFiles(string $logFilePrefix)
    {
        // Determine the directory and the merged log file
        $logDirectory = base_path('storage/logs/');
        $mergedLogFile = $logDirectory . 'workers.log';

        // Find all files that match the prefix
        $logFiles = glob($logDirectory . $logFilePrefix . '.*');

        // If no log files are found, return a message
        if (empty($logFiles)) {
            return response()->json(['message' => __('workers.label.no_workers')]);
        }

        // Open or create the merged log file
        $mergedFileHandle = fopen($mergedLogFile, 'a') ?: touch($mergedLogFile);

        // Iterate through the log files and merge their contents
        foreach ($logFiles as $logFile) {
            if (filesize($logFile) > 0) {
                fwrite($mergedFileHandle, file_get_contents($logFile));
            }
            unlink($logFile);
        }

        // Close the merged log file handle
        fclose($mergedFileHandle);
    }
    public function stopWorkers()
    {
        $scheduler_workers = WorkerController::getWorkerCount('schedule:work');
        WorkerController::killWorkerPIDs($scheduler_workers['pids']);

        Artisan::call('queue:restart');

        try {
            for ($elapsedTime = 0; $elapsedTime < 10 && $this->getWorkerCount('queue:work')['count'] > 0; $elapsedTime++) {
                usleep(500000);
            }
            $this->mergeLogFiles('worker.log');
        } catch (\Exception $e) {
            \Log::error('Log merge error: ' . $e->getMessage());
        }
        try {
            for ($elapsedTime = 0; $elapsedTime < 10 && $this->getWorkerCount('schedule:work')['count'] > 0; $elapsedTime++) {
                usleep(500000);
            }

            $this->mergeLogFiles('scheduler.log');
        } catch (\Exception $e) {
            \Log::error('Log merge error: ' . $e->getMessage());
        }
        return response()->json(['message' => __('workers.label.worker_stopped')]);
    }

    // Function to generate a unique log file name
    private static function generateLogFileName($baseName)
    {
        $i = 0;
        while (file_exists($baseName . '.' . $i)) {
            $i++;
        }
        return $baseName . '.' . $i;
    }
    // Get the number of active worker processes and their PIDs for a specific artisan command
    public static function getWorkerCount($command = 'queue:work')
    {
        $projectRoot = base_path(); // Get the root of the project
        $artisanFile = $projectRoot . '/artisan'; // Path to the artisan file
        $pids = []; // Initialize an array to store process IDs

        if (PHP_OS_FAMILY === 'Windows') {
            // Attempt to use wmic to get the command line of running PHP processes
            $cmd = 'wmic process where "name=\'php.exe\'" get CommandLine, ProcessId';
            $processes = shell_exec($cmd);

            if (!empty($processes)) {
                // Remove the first line (header) and trim the rest of the output
                $lines = array_filter(explode("\n", trim($processes)));
                $lines = array_slice($lines, 1); // Skip the first line (header)
            } else {
                // Fallback to PowerShell if wmic fails
                $cmd = 'powershell -command "Get-CimInstance Win32_Process -Filter \"Name=\'php.exe\'\" | Select-Object ProcessId, CommandLine"';
                $processes = shell_exec($cmd);
                $lines = array_filter(explode("\n", trim($processes)));
                $lines = array_slice($lines, 1); // Skip the first line (header)
            }

            $pids = []; // Initialize an empty array for storing PIDs

            foreach ($lines as $line) {
                // Match the process line with the command and the correct artisan file path
                if (preg_match('/php\s+"([^"]+)"\s+' . preg_quote($command, '/') . '/', $line, $matches)) {
                    // Ensure the real path of the matched command matches the expected artisan file
                    if (isset($matches[1]) && realpath($matches[1]) === realpath($artisanFile)) {
                        // Add the process ID to the array
                        if (preg_match('/\s+(\d+)/', $line, $pidMatch)) {
                            $pids[] = $pidMatch[1]; // Add the PID to the array
                        }
                    }
                }
            }
        } else {
            // For non-Windows systems
            $cmd = "ps aux | grep 'php' | grep '$artisanFile' | grep '$command' | grep -v grep";
            $processes = shell_exec($cmd);
            $lines = array_filter(explode("\n", trim($processes)));

            foreach ($lines as $line) {
                // Extract the PID from the process list
                if (preg_match('/^\S+\s+(\d+)\s+/', $line, $matches)) {
                    $pid = $matches[1];
                    $pids[] = $pid; // Add the PID to the array
                }
            }
        }

        // Return the process IDs and the count of workers
        return [
            'count' => count($pids),
            'pids' => $pids,
        ];
    }

    // Main method to return worker count as a JSON response
    public function get()
    {
        return response()->json(['worker_count' => $this->getWorkerCount('queue:work')['count']]);
    }

    public static function cleanRedisKey($key, $pattern)
    {
        return strpos($key, $pattern) !== false ? substr($key, strpos($key, $pattern)) : $key;
    }
}
