<?php

namespace App\Role;

use App\User;

class RoleChecker
{
    /**
     * This role checks if a user can bypass explicit permissions (Admin, Developer).
     * 
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function check(User $user, string $role)
    {
        // Admin has everything
        if ($user->hasRole(UserRole::ROLE_ADMIN)) {
            return true;
        }
        else if($user->hasRole(UserRole::ROLE_DEVELOPER)) {
            $developerRoles = UserRole::getAllowedRoles(UserRole::ROLE_DEVELOPER);

            if (in_array($role, $developerRoles)) {
                return true;
            }
        }

        return $user->hasRole($role);
    }
}