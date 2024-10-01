<?php

namespace App\Policies\Event;

use App\Models\Event;
use App\Models\User;
use App\Constants\RolePermission\Constants as RolePermissionConstants;
use App\Constants\Event\Constants as EventConstants;

class EventPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        if ($user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return true;
        }

        if ($user->id === $event->created_by) {
            return true;
        }

        if ($event->status !== EventConstants::EVENT_STATUSES['DRAFT']) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return true;
        }

        if ($user->can('create_event')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        if ($user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return true;
        }

        if ($event->organizers->contains($user->id) || $user->id === $event->created_by) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        if ($user->hasRole(roles: RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return true;
        }

        if ($user->id === $event->created_by) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        if ($user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return true;
        }

        if ($user->id === $event->created_by) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        if ($user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN'])) {
            return true;
        }

        return false;
    }
}
