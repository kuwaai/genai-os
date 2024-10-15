<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class WebConfig extends Command
{
    protected $signature = 'web:config {--kernel_endpoint=null : The new kernel endpoint URL} {--safety_guard_endpoint=null : The new Safety Guard endpoint URL}';
    protected $description = 'Quickly update the kernel and Safety Guard endpoint URLs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $kernelEndpoint = $this->option('kernel_endpoint');
        $safetyGuardEndpoint = $this->option('safety_guard_endpoint');

        $updates = [];

        if ($kernelEndpoint !== null && $kernelEndpoint !== 'null') {
            $updates['kernel_location'] = $kernelEndpoint;
        }

        if ($safetyGuardEndpoint !== null && $safetyGuardEndpoint !== 'null') {
            $updates['safety_guard_location'] = $safetyGuardEndpoint;
        }

        if (empty($updates)) {
            $this->info("Nothing changed");
            return;
        }

        foreach ($updates as $key => $value) {
            $setting = SystemSetting::where('key', "$key")->first();
            if ($setting) {
                $setting->fill(["value" => $value]);
                $setting->save();
                $this->info("$key updated to $value successfully.");
            }
        }
    }
}
