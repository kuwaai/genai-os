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
		// Append more detailed permissions for chats
		$currentTimestamp = now();
		$PermissionsToAdd = [];

		$permissions = [
			// Chat tab
			'Chat_read_export_chat',
			'Chat_update_send_message',
			'Chat_update_new_chat',
			'Chat_update_upload_file',
			'Chat_update_feedback',
			'Chat_delete_chatroom'
		];
		
		$perm_ids = Permissions::whereIn('name', $permissions)->pluck('id')->toArray();
		
		$chat_perm_id = Permissions::where("name","=","tab_Chat")->first()->id;
		
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
