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
			'tab_Cloud',
            'Cloud_read_my_files',
            'Cloud_update_upload_files',
            'Cloud_delete_my_files'
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
		
		$spec_perm_id = Permissions::where("name","=","Room_update_upload_file")->first()->id;
		
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
		$oldPermission = Permissions::where('name', 'Room_update_upload_file')->first();
		if ($oldPermission) $oldPermission->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
