<?php
namespace App;

use Core\Auth;
use Core\Database;

/**
 * Per-user notification preferences (stored in user_dashboard_config).
 * Scaffold ships with no product-specific toggles; add keys when you add modules.
 */
class UserNotificationSettings
{
    public const MODULE = 'notification_preferences';

    public static function defaultConfig(): array
    {
        return [];
    }

    public static function get(): array
    {
        $userId = Auth::id();
        if (!$userId) {
            return self::defaultConfig();
        }
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT config FROM user_dashboard_config WHERE user_id = ? AND module = ?');
        $stmt->execute([$userId, self::MODULE]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$row || $row->config === null || $row->config === '') {
            return self::defaultConfig();
        }
        $decoded = json_decode($row->config, true);
        return is_array($decoded) ? $decoded : self::defaultConfig();
    }

    public static function save(array $config): void
    {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }
        $json = json_encode($config);
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO user_dashboard_config (user_id, module, config) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE config = VALUES(config)');
        $stmt->execute([$userId, self::MODULE, $json]);
    }
}
