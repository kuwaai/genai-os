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
			// Dashboard tab
			'Chat_update_detail_feedback' => 'Permission to give detailed feedbacks',
			'Room_update_detail_feedback' => 'Permission to give detailed feedbacks'
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
		// Append more detailed permissions
		$currentTimestamp = now();
		$PermissionsToAdd = [];

		$permissions = [
			'Room_update_detail_feedback'
		];
		
		$perm_ids = Permissions::whereIn('name', $permissions)->pluck('id')->toArray();
		
		$chat_perm_id = Permissions::where("name","=","Room_update_feedback")->first()->id;
		
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
		
		// Append more detailed permissions
		$currentTimestamp = now();
		$PermissionsToAdd = [];

		$permissions = [
			'Chat_update_detail_feedback'
		];
		
		$perm_ids = Permissions::whereIn('name', $permissions)->pluck('id')->toArray();
		
		$chat_perm_id = Permissions::where("name","=","Chat_update_feedback")->first()->id;
		
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
