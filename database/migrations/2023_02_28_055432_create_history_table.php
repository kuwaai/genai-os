<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historys', function (Blueprint $table) {
            $table->id();
			$table->string('msg');
			$table->foreignId('chat_id')->references('id')->on('chats')->onDelete('cascade')->onUpdate('cascade');
			$table->boolean('isbot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historys');
    }
};
