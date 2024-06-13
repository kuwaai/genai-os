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
        $perm->fill(["name"=>"tab_Store","describe"=>"Permission for tab Store"]);
        $perm->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
