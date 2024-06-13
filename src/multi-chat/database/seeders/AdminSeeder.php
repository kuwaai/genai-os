<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permissions;
use App\Models\GroupPermissions;
use App\Models\Groups;
use DB;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction(); // Start a database transaction

            // Admin user does not exist, prompt user to enter details
            $name = $this->command->ask('Enter admin username');
            $email = $this->command->ask('Enter admin email');
            $password = $this->command->secret('Enter admin password');

            // Create a new admin user
            $admin_group = Groups::firstOrCreate(['name' => 'Admins'], ['describe' => 'Default seeded Admin group']);
            $admin_user = new User();
            $admin_user->fill([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'group_id' => $admin_group->id,
            ]);
            $admin_user->save();

            // Clear and Giving all permissions to the migrated admin group
            GroupPermissions::where('group_id', '=', $admin_group->id)->delete();
            $perm_records = [];
            $currentTimestamp = now();
            foreach (Permissions::get() as $perm) {
                $perm_records[] = [
                    'group_id' => $admin_group->id,
                    'perm_id' => $perm->id,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ];
            }
            GroupPermissions::insert($perm_records);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e; // Re-throw the exception to halt the migration
        }
    }
}
