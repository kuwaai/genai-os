<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    public $field = "cache_update_check";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = new SystemSetting();
        $setting->fill([
            'key' => $this->field,
            'value' => "",
        ]);
        $setting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', $this->field)->delete();
    }
};
