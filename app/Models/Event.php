<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Define the many-to-many relationship between Event and User (Organizers).
     */
    public function organizers()
    {
        return $this->belongsToMany(User::class, 'event_organizer', 'event_id', 'user_id');
    }
}
