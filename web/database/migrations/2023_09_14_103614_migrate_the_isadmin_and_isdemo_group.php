<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
use App\Models\GroupPermissions;
use App\Models\Groups;
use App\Models\User;
use DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction(); // Start a database transaction
            // Append more detailed permissions for profile
            $currentTimestamp = now();
            $PermissionsToAdd = [];

            $permissions = [
                // Profile tab
                'tab_Profile' => 'Permission for tab Profile',
                'Profile_update_name' => 'Permission to update name',
                'Profile_update_email' => 'Permission to update email',
                'Profile_update_password' => 'Permission to update password',
                'Profile_update_openai_token' => 'Permission to update OpenAI Token',
                'Profile_read_api_token' => 'Permission to read TAIDE Chat API token',
                'Profile_delete_account' => 'Permission to delete their account',
                'Profile_update_api_token' => 'Permission to update TAIDE Chat API token',
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

            // Migrate the old columns to new group by values
            $group = new Groups();
            $group->fill(['name' => 'Admins', 'describe' => 'The old isAdmin users are all migrated into this group']);
            $group->save();
            $admin_group_id = $group->id;
            $group = new Groups();
            $group->fill(['name' => 'Demos', 'describe' => 'The old forDemo users are all migrated into this group']);
            $group->save();
            $demo_group_id = $group->id;

            $perm_records = [];
            $currentTimestamp = now();
            // Giving all permissions to the migrated admin group
            foreach (Permissions::get() as $perm) {
                $perm_records[] = [
                    'group_id' => $admin_group_id,
                    'perm_id' => $perm->id,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ];
            }
            // For the demo accounts, only give them the permission to chat tab
            $perm_records[] = [
                'group_id' => $demo_group_id,
                'perm_id' => Permissions::where('name', '=', 'tab_Chat')->first()->id,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ];

            GroupPermissions::insert($perm_records);

            // Let these users join their groups
            User::where('forDemo', true)->update(['group_id' => $demo_group_id]);
            User::where('isAdmin', true)->update(['group_id' => $admin_group_id]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('isAdmin');
                $table->dropColumn('forDemo');
            });
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
