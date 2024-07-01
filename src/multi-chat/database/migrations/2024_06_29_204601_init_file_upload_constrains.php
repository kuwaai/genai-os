<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public $field_upload_max_size_mb = "upload_max_size_mb";
    public $field_upload_allowed_extensions = "upload_allowed_extensions";

    public function up(): void
    {
        $setting = new SystemSetting();
        $setting->fill([
            'key' => $this->field_upload_max_size_mb,
            'value' => '20',
        ]);
        $setting->save();
        
        $setting = new SystemSetting();
        $setting->fill([
            'key' => $this->field_upload_allowed_extensions,
            'value' => 'pdf,doc,docx,odt,ppt,pptx,odp,xlsx,xls,ods,eml,txt,md,csv,json,'.
                       'jpeg,jpg,gif,png,avif,webp,bmp,ico,cur,tiff,tif,'.
                       'zip' ,
        ]);
        $setting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', $this->field_upload_max_size_mb)->delete();
        SystemSetting::where('key', $this->field_upload_allowed_extensions)->delete();
    }
};
