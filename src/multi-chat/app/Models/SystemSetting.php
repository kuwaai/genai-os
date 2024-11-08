<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class SystemSetting extends Model
{
    use HasFactory;
    protected $table = 'system_setting';
    protected $fillable = ['key', 'value'];

    static function smtpConfigured()
    {
        $smtpConfig = [config('app.MAIL_MAILER'), config('app.MAIL_HOST'), config('app.MAIL_PORT'), config('app.MAIL_USERNAME'), config('app.MAIL_PASSWORD'), config('app.MAIL_ENCRYPTION'), config('app.MAIL_FROM_ADDRESS'), config('app.MAIL_FROM_NAME')];

        return count(array_filter($smtpConfig)) === count($smtpConfig);
    }
}
