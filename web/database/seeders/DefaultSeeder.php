<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permissions;
use App\Models\GroupPermissions;
use App\Models\Groups;
use DB;

class DefaultSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::where('email', 'dev@chat.gai.tw')->first();
        if ($user === null) {
            try {
                DB::beginTransaction(); // Start a database transaction
                // Assume not yet seeded, try seeding the database
                $group = new Groups();
                $group->fill(['name' => 'Admins', 'describe' => 'Default seeded Admin group']);
                $group->save();
                $admin_group_id = $group->id;
                $group = new Groups();
                $group->fill(['name' => 'Demos', 'describe' => 'Default seeded Demo group']);
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

                $user = new User();
                $user->fill([
                    'name' => 'dev',
                    'email' => 'dev@chat.gai.tw',
                    'email_verified_at' => now(),
                    'password' => Hash::make('develope'),
                    'group_id' => $admin_group_id
                ]);
                $user->save();
                \App\Models\User::factory(3)->create()->update(["group_id"=>$demo_group_id]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback the transaction in case of an exception
                throw $e; // Re-throw the exception to halt the migration
            }
        }
    }
}
