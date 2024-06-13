<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $perm = new Permissions();
        $perm->fill(["name"=>"Room_update_upload_file","describe"=>"Permission to upload file"]);
        $perm->save();
        Permissions::where("name",'=','Chat_read_access_to_api')->update(['name' => 'Room_read_access_to_api']);
        Permissions::where("name","like","Chat_%")->delete();
        Permissions::where("name","=","tab_Chat")->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
