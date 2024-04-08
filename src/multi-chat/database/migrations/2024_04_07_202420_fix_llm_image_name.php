<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LLMs;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        LLMs::where("image","=","data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGlwJ/lXeUPwAAAABJRU5ErkJggg==")->update(["image"=>null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
