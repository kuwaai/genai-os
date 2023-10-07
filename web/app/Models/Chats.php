<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
    use HasFactory;
	use SoftDeletes;
    protected $table = 'chats';
    protected $fillable = ['name', 'llm_id', 'user_id', 'dcID'];
}
