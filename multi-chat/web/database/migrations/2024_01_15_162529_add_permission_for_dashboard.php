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
            // Append more detailed permissions for Dashboard
            $currentTimestamp = now();
            $PermissionsToAdd = [];

            $permissions = [
                // Dashboard tab
                'Dashboard_read_statistics' => 'Permission to access statistics tab',
                'Dashboard_read_blacklist' => 'Permission to access blacklist tab',
                'Dashboard_read_feedbacks' => 'Permission to access feedbacks tab',
                'Dashboard_read_logs' => 'Permission to access logs tab',
                'Dashboard_read_safetyguard' => 'Permission to access safetyguard tab',
                'Dashboard_read_inspect' => 'Permission to inspect tab'
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		
    }
};
