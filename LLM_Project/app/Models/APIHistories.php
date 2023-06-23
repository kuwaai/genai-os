<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APIHistories extends Model
{
    use HasFactory;
    protected $table = 'api_histories';
    protected $fillable = ['input', 'output', 'user_id'];
}
