<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Chatroom;
use App\Models\Chats;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('llms', function (Blueprint $table) {
            if (Schema::hasColumn('llms', 'version')) {
                $table->dropColumn('version');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
