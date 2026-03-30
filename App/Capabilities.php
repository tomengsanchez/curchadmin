<?php
namespace App;

/**
 * Capability registry for the admin scaffold. Add entities here as you build features.
 */
class Capabilities
{
    private static array $entities = [
        'Settings' => [
            'view_settings' => 'View',
            'manage_settings' => 'Manage',
        ],
        'Email Settings' => [
            'view_email_settings' => 'View',
            'manage_email_settings' => 'Manage',
        ],
        'Security' => [
            'view_security_settings' => 'View',
            'manage_security_settings' => 'Manage',
        ],
        'Users' => [
            'view_users' => 'View List',
            'add_users' => 'Add',
            'edit_users' => 'Edit',
            'delete_users' => 'Delete',
            'export_users' => 'Export',
        ],
        'User Roles & Capabilities' => [
            'view_roles' => 'View List',
            'edit_roles' => 'Edit',
        ],
    ];

    private static array $menuCapability = [
        'settings' => 'view_settings',
        'email-settings' => 'view_email_settings',
        'security-settings' => 'view_security_settings',
        'users' => 'view_users',
        'user-roles' => 'view_roles',
    ];

    public static function entities(): array
    {
        return self::$entities;
    }

    public static function all(): array
    {
        $flat = [];
        foreach (self::$entities as $caps) {
            $flat = array_merge($flat, $caps);
        }
        return $flat;
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function getLabel(string $key): string
    {
        foreach (self::$entities as $caps) {
            if (isset($caps[$key])) {
                return $caps[$key];
            }
        }
        return $key;
    }

    public static function forMenu(string $page): ?string
    {
        return self::$menuCapability[$page] ?? null;
    }

    public static function registerEntity(string $entityName, array $capabilities, ?string $menuKey = null): void
    {
        self::$entities[$entityName] = $capabilities;
        $viewKey = array_key_first($capabilities);
        if ($menuKey && $viewKey) {
            self::$menuCapability[$menuKey] = $viewKey;
        }
    }
}
