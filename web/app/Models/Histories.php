<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Histories extends Model
{
    use HasFactory;
	use SoftDeletes;
    protected $table = 'histories';
    protected $fillable = ['msg', 'chat_id', 'isbot', 'created_at'];
}
