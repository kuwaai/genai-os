<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class WebConfig extends Command
{
    protected $signature = 'web:config {--settings= : Key-value pairs to update, formatted as key=value, separated by commas}';
    protected $description = 'Quickly update system settings based on key-value pairs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $settingsInput = $this->option('settings');

        if (empty($settingsInput)) {
            $this->error("No settings provided. Use the --settings option with key-value pairs.");
            return;
        }

        $updates = [];
        $settingsArray = explode(',', $settingsInput);

        foreach ($settingsArray as $setting) {
            // Ensure that the setting has a key and a value
            if (strpos($setting, '=') === false) {
                $this->warn("Invalid setting format '$setting'. Use key=value format.");
                continue;
            }

            list($key, $value) = explode('=', $setting, 2);
            $key = trim($key);
            $value = trim($value);

            // Check if the setting exists in the database
            $existingSetting = SystemSetting::where('key', $key)->first();
            if ($existingSetting) {
                $existingSetting->value = $value; // Assign the new value
                $existingSetting->save();
                $this->info("$key updated to $value successfully.");
            } else {
                $this->warn("Setting '$key' not found.");
            }
        }
    }
}
