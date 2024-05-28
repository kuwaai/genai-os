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
			// Store tab
			'Store_update_create_bot',
			'Store_update_modify_bot',
			'Store_delete_delete_bot',
			'Store_read_discover_community_bots',
			'Store_read_discover_system_bots',
			'Store_read_discover_my_bots',
			'Store_read_any_modelfile',
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
		
		$chat_perm_id = Permissions::where("name","=","tab_Store")->first()->id;
		
		$groups = GroupPermissions::where("perm_id","=",$chat_perm_id)->pluck('group_id')->toArray();
		
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
