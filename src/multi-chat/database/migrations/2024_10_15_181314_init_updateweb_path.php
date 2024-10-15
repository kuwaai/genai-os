<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    public $field = "updateweb_path";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = new SystemSetting();
        $setting->fill([
            'key' => $this->field,
            'value' => "/usr/local/bin:/usr/bin:/bin",
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
