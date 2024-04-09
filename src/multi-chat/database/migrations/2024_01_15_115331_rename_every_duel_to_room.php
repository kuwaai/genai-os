<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        try {
            Schema::table('chats', function (Blueprint $table) {
                $table->renameColumn('dcID', 'roomID');
            });

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            try {
                Schema::table('chats', function (Blueprint $table) {
                    $table->renameColumn('`dcID`', '`roomID`');
                });
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }
        DB::beginTransaction();

        try {
            Schema::rename('duelchat', 'chatrooms');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }
        
        DB::beginTransaction();
        try {
            DB::table('permissions')
            ->where('name', 'like', '%Duel%')
            ->update([
                'name' => DB::raw("REPLACE(name, 'Duel', 'Room')"),
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
