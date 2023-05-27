<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelChat extends Model
{
    use HasFactory;
    protected $table = 'duelchat';
    protected $fillable = ['name', 'user_id'];
}
