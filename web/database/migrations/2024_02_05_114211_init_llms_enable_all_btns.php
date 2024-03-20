<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LLMs;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        LLMs::whereNull("config")->update([
            "config" => '{"react_btn":["feedback","translate","quote","other"]}'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
