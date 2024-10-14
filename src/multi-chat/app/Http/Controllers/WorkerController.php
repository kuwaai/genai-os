<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class WorkerController extends Controller
{
    public function start(Request $request)
    {
        $count = $request->validate(['count' => 'required|integer|min:1'])['count'];
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
                return response()->json(['message' => 'Worker failed to start: ' . $e->getMessage()]);
            }
        }

        return response()->json(['message' => 'Workers started successfully.']);
    }

    private function generateLogFileName($baseName)
    {
        $i = 0;
        while (file_exists($baseName . '.' . $i)) {
            $i++;
        }
        return $baseName . '.' . $i;
    }

    public function get()
    {
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'tasklist /FI "IMAGENAME eq php.exe" /FO CSV' : 'ps aux | grep "php artisan queue:work" | grep -v grep';

        $processes = shell_exec($cmd);
        $count = PHP_OS_FAMILY === 'Windows' ? max(0, count(explode("\n", $processes)) - 2) : count(explode("\n", trim($processes)));

        return response()->json(['worker_count' => $count]);
    }

    public static function cleanRedisKey($key, $pattern)
    {
        return strpos($key, $pattern) !== false ? substr($key, strpos($key, $pattern)) : $key;
    }
    public function stop()
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
                return response()->json(['message' => 'No workers opened.']);
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

        return response()->json(['message' => 'All workers stopped and logs merged.']);
    }
}
