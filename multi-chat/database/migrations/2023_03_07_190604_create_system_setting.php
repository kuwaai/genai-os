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
        Schema::create('system_setting', function (Blueprint $table) {
            $table->id();
			$table->string('key');
			$table->string('value');
            $table->timestamps();
        });
		$setting = new SystemSetting();
		$setting->fill([
			'key' => 'allowRegister',
			'value' => 'false',
		]);
		$setting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_setting');
    }
};
