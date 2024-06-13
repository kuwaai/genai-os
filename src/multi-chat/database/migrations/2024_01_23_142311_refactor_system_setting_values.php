<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		
        function extractBaseUrl($url)
        {
            $parsedUrl = parse_url($url);

            // Check if the URL has a scheme (http, https)
            if (isset($parsedUrl['scheme'])) {
                $baseUrl = $parsedUrl['scheme'] . '://';
            } else {
                $baseUrl = '';
            }

            // Check if the URL has a host (domain)
            if (isset($parsedUrl['host'])) {
                $baseUrl .= $parsedUrl['host'];

                // Include port if present
                if (isset($parsedUrl['port'])) {
                    $baseUrl .= ':' . $parsedUrl['port'];
                }
            }

            return $baseUrl;
        }
		$setting = SystemSetting::where('key','=','agent_location')->first();
		$setting->value = extractBaseUrl($setting->value);
		$setting->save();
		$setting = SystemSetting::where('key','=','safety_guard_location')->first();
		$setting->value = extractBaseUrl($setting->value);
		$setting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
