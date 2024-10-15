<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    public $field = "agent_location";
    public $to_field = "kernel_location";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SystemSetting::where('key', $this->field)
            ->update(['key' => $this->to_field]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', $this->to_field)
            ->update(['key' => $this->field]);
    }
};
