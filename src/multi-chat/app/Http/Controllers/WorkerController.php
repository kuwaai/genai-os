<?php

namespace App\Http\Controllers;

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
            $command = PHP_OS_FAMILY === 'Windows' ? "start /B php {$artisanPath} queue:work >> {$logFile} 2>&1" : "php {$artisanPath} queue:work >> {$logFile} 2>&1 &";

            try {
                Process::fromShellCommandline($command)->start();
                while (!file_exists($logFile)) {
                    usleep(100000);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => __('manage.label.worker_start_failed') . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => __('manage.label.worker_started')]);
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
                return response()->json(['message' => __('manage.label.no_workers')]);
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

        return response()->json(['message' => __('manage.label.worker_stopped')]);
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

    // Get the number of active worker processes
    public function get()
    {
        $projectRoot = base_path();
        $artisanFile = $projectRoot . '/artisan';

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'tasklist /FI "IMAGENAME eq php.exe" /FO CSV';
            $processes = shell_exec($cmd);

            $count = max(0, count(explode("\n", $processes)) - 2);
        } else {
            $cmd = "lsof -t '$artisanFile' | xargs ps -p | grep 'php' | grep -v grep";
            $processes = shell_exec($cmd);

            if (empty(trim($processes))) {
                $cmd = "ps aux | grep 'php' | grep '$artisanFile' | grep -v grep";
                $processes = shell_exec($cmd);
            }

            $count = count(array_filter(explode("\n", trim($processes))));
        }

        return response()->json(['worker_count' => $count]);
    }

    public static function cleanRedisKey($key, $pattern)
    {
        return strpos($key, $pattern) !== false ? substr($key, strpos($key, $pattern)) : $key;
    }
}
