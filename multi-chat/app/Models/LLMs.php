<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LLMs extends Model
{
    use HasFactory;
    protected $table = 'llms';
    protected $fillable = ['image', 'name', 'link', 'limit_per_day', "access_code", "order", 'enabled', "version", "description"];
}
