<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Bots;
use App\Models\LLMs;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction(); // Start a database transaction

            Schema::create('bots', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('name');
                $table->string('type');
                $table->integer('visibility');
                $table->foreignId('model_id')->nullable()->references('id')->on('llms')->onDelete('cascade')->onUpdate('cascade');
                $table->foreignId('owner_id')->nullable()->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->string('description')->nullable();
                $table->string('config')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
            foreach (LLMs::get() as $row) {
                $bot = new Bots();
                $bot->fill(['name' => $row->name, 'type' => 'prompt', 'visibility' => 0, "model_id"=>$row->id]);
                $bot->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e; // Re-throw the exception to halt the migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::beginTransaction(); // Start a database transaction
    
            Schema::dropIfExists('bots');
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e; // Re-throw the exception to halt the migration
        }
    }
};
