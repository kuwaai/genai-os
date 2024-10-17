<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WorkerController;

class StartWorkers extends Command
{
    protected $signature = 'workers:start {count=10}';
    protected $description = 'Start specified number of queue workers';

    public function handle()
    {
        // Retrieve the count from the command argument
        $count = (int) $this->argument('count');

        // Ensure the count does not exceed the limit of 30 workers
        if ($count > 30) {
            $this->error('You cannot start more than 30 workers at the same time.');
            return 1; // Indicate failure
        }

        // Create an instance of the WorkerController
        $workerController = new WorkerController();

        // Call the startWorkers function directly
        $response = $workerController->startWorkers($count);

        // Handle the response from the controller function
        if ($response->status() === 200) {
            $this->info($response->getData()->message);
            return 0; // Indicate success
        } else {
            $this->error($response->getData()->message);
            return 1; // Indicate failure
        }
    }
}
