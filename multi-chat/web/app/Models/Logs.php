<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logs';
    protected $fillable = ['action', 'description', 'user_id', 'ip_address'];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    // To prevent updates after creation
    public function save(array $options = [])
    {
        if ($this->exists) {
            return false;
        }

        return parent::save($options);
    }

    // To prevent updates after creation
    public function update(array $attributes = [], array $options = [])
    {
        return false;
    }
}
