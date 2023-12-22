<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            // Append more detailed permissions for chats
            $currentTimestamp = now();
            $PermissionsToAdd = [];

            $permissions = [
                // Chat tab
                'Duel_read_export_chat' => 'Permission to export chat history',
                'Duel_update_import_chat' => 'Permission to import history',
                'Duel_update_new_chat' => 'Permission to create new chat',
                'Duel_update_feedback' => 'Permission to use feedback',
                'Duel_delete_chatroom' => 'Permission to delete chatroom',
                'Duel_update_send_message' => 'Permission to import history',
                'Chat_update_react_message' => 'Permission to use extra react buttons'
            ];

            foreach ($permissions as $name => $describe) {
                $PermissionsToAdd[] = [
                    'name' => $name,
                    'describe' => $describe,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
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
