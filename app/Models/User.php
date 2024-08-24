<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Constants\RolePermission\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at'
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function highest_role(): Role
    {
        $highest_role = Role::findByName(Constants::DEFAULT_ROLES['ATTENDEE']);
        $highest_level = 0;

        foreach ($this->roles as $role) {
            $role_name = $role->name;
            $role_level = Constants::ROLE_HIERARCHY[$role_name] ?? 0;

            if ($role_level > $highest_level) {
                $highest_level = $role_level;
                $highest_role = $role;
            }
        }

        return $highest_role;
    }
}
