<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Histories extends Model
{
    use HasFactory;
    protected $table = 'histories';
    protected $fillable = ['msg', 'chat_id', 'isbot'];
}
