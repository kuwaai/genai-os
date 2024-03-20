<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
use App\Models\LLMs;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->longText('describe')->nullable();
			$table->string('invite_token')->nullable();
            $table->timestamps();
        });
		
		Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->longText('describe')->nullable();
            $table->timestamps();
        });
		
		Schema::create('group_permissions', function (Blueprint $table) {
            $table->id();
			$table->foreignId('group_id')->references('id')->on('groups')->onDelete('cascade')->onUpdate('cascade');
			$table->foreignId('perm_id')->references('id')->on('permissions')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
		
		Schema::table('users', function (Blueprint $table) {
			$table->foreignId('group_id')->nullable()->references('id')->on('groups')->onDelete('cascade')->onUpdate('cascade');
        });
		
		$tabNames = ["Dashboard", "Duel", "Chat", "Archive", "Play", "Manage"];
		$currentTimestamp = now();
		$PermissionsToAdd = [];

		// Tab Permissions init
		foreach ($tabNames as $tabName) {
			$PermissionsToAdd[] = [
				'name' => 'tab_' . $tabName,
				'describe' => 'Permission for tab ' . $tabName,
				'created_at' => $currentTimestamp,
				'updated_at' => $currentTimestamp
			];
		}

		// Model Permission init
		foreach (LLMs::get() as $LLM){
			$PermissionsToAdd[] = [
				'name' => 'model_' . $LLM->id,
				'describe' => 'Permission for model id ' . $LLM->id,
				'created_at' => $currentTimestamp,
				'updated_at' => $currentTimestamp
    
			];
		}

		// Insert all permissions into db
		Permissions::insert($PermissionsToAdd);
		
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
