<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permissions;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Permissions::where('name', '=', 'Room_read_access_to_api')->exists()) {
            $perm = new Permissions();
            $perm->name = 'Room_read_access_to_api';
            $perm->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
