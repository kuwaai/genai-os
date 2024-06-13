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
        Schema::table('system_setting', function (Blueprint $table) {
			$table->longText('value')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
