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
        $settingsInput = $this->option('settings');

        if (!$settingsInput) {
            $this->error("No settings provided. Use the --settings option with key-value pairs.");
            return;
        }

        $updates = [];
        $settingsArray = explode(',', $settingsInput);

        foreach ($settingsArray as $setting) {
            list($key, $value) = explode('=', $setting);
            $key = trim($key);
            $value = trim($value);

            if (in_array($key, ['kernel_location', 'safety_guard_location'])) {
                $updates[$key] = $value;
            } else {
                $this->warn("Invalid key '$key' provided. Allowed keys are: kernel_location, safety_guard_location.");
            }
        }

        if (empty($updates)) {
            $this->info("Nothing changed or no valid updates.");
            return;
        }

        foreach ($updates as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $setting->fill(['value' => $value]);
                $setting->save();
                $this->info("$key updated to $value successfully.");
            } else {
                $this->warn("Setting '$key' not found.");
            }
        }
    }
}
