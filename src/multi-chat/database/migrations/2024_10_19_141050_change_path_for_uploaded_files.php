<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up()
    {
        $source = storage_path('app/public/homes');
        $destination = storage_path('app/public/root/homes');

        if (File::exists($source)) {
            File::makeDirectory(dirname($destination), 0755, true);
            if (File::copyDirectory($source, $destination)) {
                File::deleteDirectory($source);
                \Log::info("Moved directory from $source to $destination");
            } else {
                \Log::error("Failed to move directory from $source to $destination");
            }
        }
    }

    public function down()
    {
        $source = storage_path('app/public/root/homes');
        $destination = storage_path('app/public/homes');

        if (File::exists($source)) {
            File::makeDirectory(dirname($destination), 0755, true);
            if (File::copyDirectory($source, $destination)) {
                File::deleteDirectory($source);
                \Log::info("Moved back directory from $source to $destination");
            } else {
                \Log::error("Failed to move directory back from $source to $destination");
            }
        }
    }
};