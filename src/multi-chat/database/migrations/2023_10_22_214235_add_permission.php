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
        // Append more detailed permissions for chats
        $currentTimestamp = now();
        $PermissionsToAdd = [];

        $permissions = [
            // Chat tab
            'Chat_read_export_chat' => 'Permission to export chat history',
            'Chat_update_import_chat' => 'Permission to import history',
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
