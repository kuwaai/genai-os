<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'feedback';
    protected $fillable = ['history_id', 'detail', 'flags', 'nice'];
}
