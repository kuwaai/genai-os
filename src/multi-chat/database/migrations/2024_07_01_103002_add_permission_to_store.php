<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
use App\Models\GroupPermissions;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		// Append more detailed permissions
		$currentTimestamp = now();
		$PermissionsToAdd = [];

		$permissions = [
			'Store_create_group_bot',
			'Store_create_private_bot'
		];

		foreach ($permissions as $name) {
			$PermissionsToAdd[] = [
				'name' => $name,
				'created_at' => $currentTimestamp,
				'updated_at' => $currentTimestamp,
			];
		}
		// Insert all permissions into db
		Permissions::insert($PermissionsToAdd);
		
		$perm_ids = Permissions::whereIn('name', $permissions)->pluck('id')->toArray();
		
		$spec_perm_id = Permissions::where("name","=","Store_update_create_bot")->first()->id;
		
		$groups = GroupPermissions::where("perm_id","=",$spec_perm_id)->pluck('group_id')->toArray();
		
		foreach($groups as $group){
			GroupPermissions::where('group_id', $group)->whereIn('perm_id', $perm_ids)->delete();
			$records = [];
			foreach($perm_ids as $perm_id){
				$records[] = [
					"group_id"=>$group,
					"perm_id" => $perm_id,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
				];
			}
            GroupPermissions::insert($records);
		}
		// Check if the old permission exists and update its name
		$oldPermission = Permissions::where('name', 'Store_update_create_bot')->first();
		if ($oldPermission) {
			$oldPermission->name = 'Store_create_community_bot';
			$oldPermission->updated_at = $currentTimestamp;
			$oldPermission->save();
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
