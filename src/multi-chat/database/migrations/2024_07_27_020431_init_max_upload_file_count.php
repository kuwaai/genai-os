<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{


    public $field_upload_max_file_count = "upload_max_file_count";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = new SystemSetting();
        $setting->fill([
            'key' => $this->field_upload_max_file_count,
            'value' => "-1",
        ]);
        $setting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', $this->field_upload_max_file_count)->delete();
    }
};
