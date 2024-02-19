<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Bots extends Model
{
    use HasFactory;
	use SoftDeletes;
    protected $table = 'bots';
    protected $fillable = ['image', 'name', 'type', 'visibility', "model_id", 'owner_id', 'description', 'config'];
}
