<?php

namespace App\Constants\RolePermission;

class Constants
{
    public const ROLE_CREATE_SUCCESS = 'Role has been created';
    public const ROLE_CREATE_ERROR = 'Role could not be created';
    public const ROLE_UPDATE_SUCCESS = 'Role has been updated';
    public const ROLE_UPDATE_ERROR = 'Can not update the role';
    public const ROLE_DELETE_SUCCESS = 'Role has been deleted';
    public const ROLE_DELETE_ERROR = 'Can not delete role with existing users';
    public const PERMISSION_CREATE_SUCCESS = 'Permission has been created';
    public const PERMISSION_CREATE_ERROR = 'Permission could not be created';
    public const PERMISSION_UPDATE_SUCCESS = 'Permission has been updated';
    public const PERMISSION_UPDATE_ERROR = 'Can not update the permission';
    public const PERMISSION_DELETE_SUCCESS = 'Permission has been deleted';
    public const PERMISSION_DELETE_ERROR = 'Can not delete permission';

    public const RESPONSE_MESSAGE = [
        'ACCESS_CONTROL' => [
            'ADD_PERMISSION_TO_ROLE_SUCCESS' => 'Permission added to role successfully',
            'PERMISSION_TO_ROLE_ALREADY_EXIST' => 'Role already has this permission',
            'REMOVE_PERMISSION_FROM_ROLE_SUCCESS' => 'Permission removed from role successfully',
            'ROLE_DOES_NOT_HAVE_PERMISSION' => 'Role does not have this permission',
            'ADD_PERMISSION_TO_USER_SUCCESS' => 'Permission added to user successfully',
            'PERMISSION_TO_USER_ALREADY_EXIST' => 'User already has this permission',
            'REMOVE_PERMISSION_FROM_USER_SUCCESS' => 'Permission removed from user successfully',
            'USER_DOES_NOT_HAVE_PERMISSION' => 'User does not have this permission'
        ],
    ];

    public const DEFAULT_ROLES = [
        'ADMIN' => 'admin',
        'ORGANIZER' => 'organizer',
        'ATTENDEE' => 'attendee'
    ];

    public const ROLE_HIERARCHY = [
        'admin' => 2,
        'organizer' => 1,
        'attendee' => 0
    ];
}
