<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use App\Constants\Event\Constants as EventConstants;
use App\Constants\RolePermission\Constants as RolePermissionConstants;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
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
        $highest_role = Role::findByName(RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']);
        $highest_level = 0;

        foreach ($this->roles as $role) {
            $role_name = $role->name;
            $role_level = RolePermissionConstants::ROLE_HIERARCHY[$role_name] ?? 0;

            if ($role_level > $highest_level) {
                $highest_level = $role_level;
                $highest_role = $role;
            }
        }

        return $highest_role;
    }

    /**
     * Define the many-to-many relationship between User and Event (Organized Events).
     */
    public function organized_events()
    {
        return $this->belongsToMany(Event::class, 'event_organizer', 'user_id', 'event_id');
    }

    public function get_visible_events()
    {
        if ($this->hasRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return Event::all(); // Admin can see all events
        } else {
            return Event::where('status', '!=', EventConstants::EVENT_STATUSES['DRAFT'])
                ->orWhere(function ($query) {
                    $query->where('status', EventConstants::EVENT_STATUSES['DRAFT'])
                        ->where(function ($q) {
                            $q->where('created_by', $this->id) // Non-admins can see their own drafts
                                ->orWhereHas('organizers', function ($q) {
                                    $q->where('user_id', $this->id); // Non-admins can also see drafts where they are organizers
                                });
                        });
                })->get();
        }
    }

}
