<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use App\Models\Permissions;

return new class extends Migration
{
    public function up()
    {
        Permissions::where('name', '=', 'tab_Archive')->delete();
    }
};