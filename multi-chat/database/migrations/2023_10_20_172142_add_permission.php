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
                'Chat_read_access_to_api' => 'Permission to use chat API',
                'Chat_update_send_message' => 'Permission to send message in chat',
                'Chat_update_new_chat' => 'Permission to create new chat',
                'Chat_update_upload_file' => 'Permission to upload file',
                'Chat_update_feedback' => 'Permission to use feedback',
                'Chat_delete_chatroom' => 'Permission to delete chatroom'
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
