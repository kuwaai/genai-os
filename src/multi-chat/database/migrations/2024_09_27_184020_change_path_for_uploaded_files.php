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
	public function up()
    {
        $sourcePath = storage_path('app/public/pdfs');
        $destinationPath = storage_path('app/public/homes');

        if (File::exists($sourcePath)) {
            // Move the entire directory
            File::moveDirectory($sourcePath, $destinationPath);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sourcePath = storage_path('app/public/homes');
        $destinationPath = storage_path('app/public/pdfs');

        if (File::exists($sourcePath)) {
            // Move back to the original directory
            File::moveDirectory($sourcePath, $destinationPath);
        }
    }
};
