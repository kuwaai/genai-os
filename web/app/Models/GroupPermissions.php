<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPermissions extends Model
{
    use HasFactory;
    protected $table = 'group_permissions';
    protected $fillable = ['group_id', 'perm_id'];
}
