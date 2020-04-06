<?php

namespace App\Role;

class UserRole {

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_DEVELOPER = 'ROLE_DEVELOPER';
    const ROLE_SUPPORT = 'ROLE_SUPPORT';

    const ROLE_SELLER = 'ROLE_SELLER';
    const ROLE_TRADER = 'ROLE_TRADER';

    /**
     * Array listing hierarchy of different roles within the application.
     * @var array
     */
    protected static $roleHierarchy = [
        self::ROLE_ADMIN => ['*'],
        self::ROLE_DEVELOPER => [
            self::ROLE_SUPPORT
        ],
        self::ROLE_SUPPORT => [],
        self::ROLE_SELLER => [],
        self::ROLE_TRADER => [],
    ];

    /**
     * Check if having a role grants permissions to another role.
     * 
     * @param string $role
     * @return array
     */
    public static function getAllowedRoles(string $role)
    {
        if (isset(self::$roleHierarchy[$role])) {
            return self::$roleHierarchy[$role];
        }

        return [];
    }

    /**
     * Return the list of roles.
     * 
     * @return array
     */
    public static function getRoleList()
    {
        return [
            static::ROLE_ADMIN =>'Admin',
            static::ROLE_DEVELOPER => 'Developer',
            static::ROLE_SUPPORT => 'Support',
            static::ROLE_SELLER => 'Seller',
            static::ROLE_TRADER => 'Trader',
        ];
    }

}