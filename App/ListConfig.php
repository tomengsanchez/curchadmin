<?php
namespace App;

use Core\Auth;
use Core\Database;

class ListConfig
{
    private static array $configs = [
        'users' => [
            ['key' => 'id', 'label' => 'ID', 'sortable' => true],
            ['key' => 'username', 'label' => 'Username', 'sortable' => true],
            ['key' => 'display_name', 'label' => 'Display name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'role_name', 'label' => 'Role', 'sortable' => true],
        ],
        'roles' => [
            ['key' => 'name', 'label' => 'Role', 'sortable' => true],
            ['key' => 'capabilities', 'label' => 'Capabilities', 'sortable' => false],
        ],
    ];

    private static array $exportColumns = [
        'users' => [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'username', 'label' => 'Username'],
            ['key' => 'display_name', 'label' => 'Display Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'role_name', 'label' => 'Role'],
            ['key' => 'created_at', 'label' => 'Created At'],
            ['key' => 'updated_at', 'label' => 'Updated At'],
        ],
    ];

    public static function getExportColumns(string $module): array
    {
        return self::$exportColumns[$module] ?? self::getColumns($module);
    }

    public static function getColumns(string $module): array
    {
        return self::$configs[$module] ?? [];
    }

    public static function getDefaultKeys(string $module): array
    {
        $cols = self::getColumns($module);
        return array_column($cols, 'key');
    }

    public static function getColumnByKey(string $module, string $key): ?array
    {
        foreach (self::getColumns($module) as $col) {
            if ($col['key'] === $key) {
                return $col;
            }
        }
        foreach (self::getExportColumns($module) as $col) {
            if ($col['key'] === $key) {
                return array_merge($col, ['sortable' => $col['sortable'] ?? false]);
            }
        }
        return null;
    }

    public static function resolveSelectedKeys(string $module, ?string $param, ?array $session): array
    {
        $defaults = self::getDefaultKeys($module);
        $validKeys = array_column(self::getExportColumns($module), 'key');
        if (!empty($param)) {
            $requested = array_map('trim', explode(',', $param));
            return array_values(array_intersect($requested, $validKeys)) ?: $defaults;
        }
        if (!empty($session)) {
            return array_values(array_intersect($session, $validKeys)) ?: $defaults;
        }
        return $defaults;
    }

    public static function resolveFromRequest(string $module, ?array $get = null, ?array $session = null): array
    {
        $get = $get ?? $_GET ?? [];
        $param = $get['columns'] ?? null;
        if (empty($param) && !empty($get['col']) && is_array($get['col'])) {
            $param = implode(',', array_map('trim', $get['col']));
        }

        $userPrefs = self::getUserColumns($module);
        $resolved = self::resolveSelectedKeys($module, $param, $session ?? $userPrefs);

        if (!empty($param) && Auth::id()) {
            self::saveUserColumns(Auth::id(), $module, $resolved);
        }

        return $resolved;
    }

    public static function getUserColumns(string $module): ?array
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT column_keys FROM user_list_columns WHERE user_id = ? AND module = ?');
        $stmt->execute([$userId, $module]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$row || $row->column_keys === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $row->column_keys))));
    }

    public static function saveUserColumns(int $userId, string $module, array $columnKeys): void
    {
        $columnKeys = array_values(array_filter($columnKeys));
        $keysStr = implode(',', $columnKeys);

        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO user_list_columns (user_id, module, column_keys) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE column_keys = VALUES(column_keys)');
        $stmt->execute([$userId, $module, $keysStr]);
    }

    public static function hasCustomColumns(string $module): bool
    {
        return Auth::id() && self::getUserColumns($module) !== null;
    }
}
