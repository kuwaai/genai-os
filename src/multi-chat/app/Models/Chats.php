<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Chats extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'chats';
    protected $fillable = ['bot_id', 'user_id', 'roomID'];

    public static function getChatsFromChatRoom(int $userId, int $roomId)
    {
        return self::join('bots', 'bots.id', '=', 'bot_id')->join('llms', 'llms.id', '=', 'bots.model_id')->select('llms.*', 'bots.*', 'chats.*', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'))->where('user_id', $userId)->where('roomID', $roomId)->orderBy('bot_id')->get();
    }
}
