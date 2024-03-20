<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class InitSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (empty(env('API_Key'))) {
            // Generate a random API key
			$apiKey = Str::random(32);

			// Set the API key in the configuration
			config(['app.API_Key' => $apiKey]);

			// Save the API key to the .env file, overriding existing key
			file_put_contents(base_path('.env'), str_replace(
				'API_Key=' . env('API_Key'),
				'API_Key=' . $apiKey,
				file_get_contents(base_path('.env'))
			));

            // Reload the .env file to reflect the changes
            Artisan::call('config:clear');
        }
    }
}
