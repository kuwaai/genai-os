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
		// Append more detailed permissions for Dashboard
		$currentTimestamp = now();
		$PermissionsToAdd = [];

		$permissions = [
			// Dashboard tab
			'Dashboard_read_statistics',
			'Dashboard_read_blacklist',
			'Dashboard_read_feedbacks',
			'Dashboard_read_logs',
			'Dashboard_read_safetyguard',
			'Dashboard_read_inspect'
		];
		
		$perm_ids = Permissions::whereIn('name', $permissions)->pluck('id')->toArray();
		
		$chat_perm_id = Permissions::where("name","=","tab_Dashboard")->first()->id;
		
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
