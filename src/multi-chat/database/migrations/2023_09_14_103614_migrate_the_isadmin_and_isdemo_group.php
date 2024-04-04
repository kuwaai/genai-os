<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
use App\Models\GroupPermissions;
use App\Models\Groups;
use App\Models\User;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction(); // Start a database transaction
        try {
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

            // Check if there are users for demo
            $demoUsersCount = User::where('forDemo', true)->count();
            // Check if there are admin users
            $adminUsersCount = User::where('isAdmin', true)->count();

            // Create groups only if there are corresponding users
            if ($demoUsersCount > 0) {
                $demoGroup = new Groups();
                $demoGroup->fill(['name' => 'Demos', 'describe' => 'The old forDemo users are all migrated into this group']);
                $demoGroup->save();
                User::where('forDemo', true)->update(['group_id' => $demoGroup->id]);
            }

            if ($adminUsersCount > 0) {
                $adminGroup = new Groups();
                $adminGroup->fill(['name' => 'Admins', 'describe' => 'The old isAdmin users are all migrated into this group']);
                $adminGroup->save();
                // Migrate permissions for admin group
                $perm_records = [];
                foreach (Permissions::get() as $perm) {
                    $perm_records[] = [
                        'group_id' => $adminGroup->id,
                        'perm_id' => $perm->id,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ];
                }
                GroupPermissions::insert($perm_records);
                // Assign users to their respective groups
                User::where('isAdmin', true)->update(['group_id' => $adminGroup->id]);
            }

            // Remove old columns
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['isAdmin', 'forDemo']);
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
