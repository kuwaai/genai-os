<?php

use App\Models\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $permissions = [
        'Room_update_ignore_upload_constraint'
    ];
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Append more detailed permissions
        $currentTimestamp = now();
        $PermissionsToAdd = [];

        foreach ($this->permissions as $name) {
            $PermissionsToAdd[] = [
                'name' => $name,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ];
        }
        // Insert all permissions into db
        Permissions::insert($PermissionsToAdd);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->permissions as $name) {
            Permissions::where('name', $name)->delete();
        }
    }
};
