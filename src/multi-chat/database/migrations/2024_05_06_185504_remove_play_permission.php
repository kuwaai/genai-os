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
		Permissions::where("name","=","tab_Play")->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
