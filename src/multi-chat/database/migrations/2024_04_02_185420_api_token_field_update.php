<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
			$table->string('google_token')->nullable();
        });

        Permissions::where("name", "=", "Profile_update_openai_token")->update(["name" => "Profile_update_external_api_token"]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
