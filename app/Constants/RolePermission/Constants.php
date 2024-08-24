<?php

namespace App\Constants\RolePermission;

class Constants
{
    public const DEFAULT_ROLES = [
        'ADMIN' => 'admin',
        'ORGANIZER' => 'organizer',
        'ATTENDEE' => 'attendee',
    ];

    public const ROLE_HIERARCHY = [
        'admin' => 2,
        'organizer' => 1,
        'attendee' => 0,
    ];
}
