<?php

namespace App\Models;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\GroupPermissions;
use App\Models\Group;

class User extends Authenticatable implements MustVerifyEmail, LdapAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
		'openai_token',
        'group_id',
        'term_accepted',
        'guid',
        'domain',
        'detail',
        'require_change_password',
        'google_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function hasPerm($permissions)
    {
        if ($this->group_id) {
            // If $permissions is not an array, convert it to an array
            if (!is_array($permissions)) {
                $permissions = [$permissions];
            }
            
            // Get the permission IDs for all provided permissions
            $perm_ids = Permissions::whereIn("name", $permissions)->pluck('id')->toArray();
            
            // Check if any of the permissions exist for the group
            return GroupPermissions::where("group_id", $this->group_id)
                                   ->whereIn("perm_id", $perm_ids)
                                   ->exists();
        }
        return false;
    }
    
}
