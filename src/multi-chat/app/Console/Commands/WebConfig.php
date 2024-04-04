<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class WebConfig extends Command
{
    protected $signature = 'web:config {--kernel_endpoint=null : The new endpoint URL}';
    protected $description = 'Quickly update the kernel endpoint URL';
    
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $endpoint = $this->option('kernel_endpoint');
        if ($endpoint !== null && $endpoint !== 'null'){
            $kernel = SystemSetting::where('key', 'agent_location')->first();
            $kernel->fill(["value"=>$endpoint]);
            $kernel->save();
            $this->info("Kernel endpoint updated successfully.");
        } else {
            $this->info("Nothing changed");
        }
    }
    
    
}
