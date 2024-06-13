<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $loc = SystemSetting::where("key","=","agent_location")->where("value","=","http://localhost:9000");
        if ($loc->exists()){
            $loc = $loc->first();
            $loc->value = "http://127.0.0.1:9000";
            $loc->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
