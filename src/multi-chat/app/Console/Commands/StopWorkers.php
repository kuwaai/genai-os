<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WorkerController;

class StopWorkers extends Command
{
    protected $signature = 'workers:stop';
    protected $description = 'Stop all queue workers';

    public function handle()
    {
        // Instantiate the WorkerController
        $controller = new WorkerController();

        // Use the stopWorkers function to stop all workers
        $response = $controller->stopWorkers();

        // Check if the response indicates success
        if ($response->status() === 200) {
            $this->info($response->getData()->message);
        } else {
            $this->error($response->getData()->message);
        }

        return 0; // Indicate success
    }
}
