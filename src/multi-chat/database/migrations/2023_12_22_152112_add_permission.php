<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
use App\Models\GroupPermissions;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction(); // Start a database transaction
        try {
            // Append more detailed permissions for profile
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
                'Duel_update_send_message' => 'Permission to send message in chat',
                'Duel_update_react_message' => 'Permission to use extra react buttons',
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

            // Append more detailed permissions for chats
            $currentTimestamp = now();
            $PermissionsToAdd = [];
            //Duel tab permission init
            $permissions = [
                // Duel tab
                'Duel_read_export_chat',
                'Duel_update_import_chat',
                'Duel_update_new_chat',
                'Duel_update_feedback',
                'Duel_delete_chatroom',
                'Duel_update_send_message',
                'Duel_update_react_message'
            ];

            $perm_ids = Permissions::whereIn('name', $permissions)
                ->pluck('id')
                ->toArray();

            $chat_perm_id = Permissions::where('name', '=', 'tab_Duel')->first()->id;

            $groups = GroupPermissions::where('perm_id', '=', $chat_perm_id)
                ->pluck('group_id')
                ->toArray();

            foreach ($groups as $group) {
                GroupPermissions::where('group_id', $group)
                    ->whereIn('perm_id', $perm_ids)
                    ->delete();
                $records = [];
                foreach ($perm_ids as $perm_id) {
                    $records[] = [
                        'group_id' => $group,
                        'perm_id' => $perm_id,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ];
                }
                GroupPermissions::insert($records);
            }
            //Chat tab permissions init
            $permissions = [
                // Chat tab
                'Chat_update_react_message',
            ];

            $perm_ids = Permissions::whereIn('name', $permissions)
                ->pluck('id')
                ->toArray();

            $chat_perm_id = Permissions::where('name', '=', 'tab_Duel')->first()->id;

            $groups = GroupPermissions::where('perm_id', '=', $chat_perm_id)
                ->pluck('group_id')
                ->toArray();

            foreach ($groups as $group) {
                GroupPermissions::where('group_id', $group)
                    ->whereIn('perm_id', $perm_ids)
                    ->delete();
                $records = [];
                foreach ($perm_ids as $perm_id) {
                    $records[] = [
                        'group_id' => $group,
                        'perm_id' => $perm_id,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ];
                }
                GroupPermissions::insert($records);
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
    }
};
