<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \DB;

class LLMs extends Model
{
    use HasFactory;
    protected $table = 'llms';
    protected $fillable = ['image', 'name', "access_code", "order", 'enabled', "description", "config"];
    static function getModelPermIds()
    {
        return DB::table(function ($query) {
            $query->select(DB::raw('substring(name, 7) as model_id, id'))
            ->from('permissions')->where('name', 'like', 'model_%');
        }, 'p')
        ->join('llms', DB::raw('CAST(llms.id AS '. (config('database.default') == "mysql" ? 'CHAR' : 'TEXT') .')'), '=', 'p.model_id')
        ->select('p.id as id', 'llms.name as name');
    }
    static function getLLMs($group_id, $enabled = true)
    {
        return DB::table(function ($query) use ($group_id) {
            $query->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')->from('group_permissions')->join('permissions', 'perm_id', '=', 'permissions.id')->where('group_id', $group_id)->where('name', 'like', 'model_%')->get();
        }, 'tmp')
            ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
            ->select('tmp.*', 'llms.*')
            ->where('llms.enabled', $enabled)
            ->orderby('llms.order')
            ->orderby('llms.created_at')
            ->get();
    }
}
