<?php
namespace App;

use Core\Database;
use App\Models\AppSettings;

/**
 * In-app notifications. Extend with domain-specific notify* methods when you add modules.
 */
class NotificationService
{
    public const RELATED_SYSTEM = 'system';

    public static function getForUser(int $userId, int $limit = 20): array
    {
        $db = Database::getInstance();
        $limit = max(1, min(100, (int) $limit));
        $sql = 'SELECT id, type, related_type, related_id, project_id, message, created_at, clicked_at
                FROM notifications
                WHERE user_id = ? AND (clicked_at IS NULL)
                ORDER BY created_at DESC
                LIMIT ' . $limit;
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public static function listForUser(int $userId, array $filters, int $page = 1, int $perPage = 20): array
    {
        $db = Database::getInstance();
        $page = max(1, (int) $page);
        $perPage = max(1, min(100, (int) $perPage));

        $where = ['n.user_id = :uid'];
        $params = ['uid' => $userId];

        $module = $filters['module'] ?? '';
        if ($module !== '') {
            $where[] = 'n.related_type = :rtype';
            $params['rtype'] = $module;
        }
        if (!empty($filters['project_id'])) {
            $where[] = 'n.project_id = :pid';
            $params['pid'] = (int) $filters['project_id'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'n.created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[] = 'n.created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }

        $whereSql = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT n.id, n.type, n.related_type, n.related_id, n.project_id, n.message, n.created_at, n.clicked_at
            FROM notifications n
            WHERE {$whereSql}
            ORDER BY n.created_at DESC, n.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $countSql = "SELECT COUNT(*) FROM notifications n WHERE {$whereSql}";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public static function clickAndGetUrl(int $notificationId, int $userId): ?string
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id, related_type, related_id FROM notifications WHERE id = ? AND user_id = ?');
        $stmt->execute([$notificationId, $userId]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$row) {
            return null;
        }
        $db->prepare('UPDATE notifications SET clicked_at = NOW() WHERE id = ?')->execute([$notificationId]);
        if ($row->related_type === self::RELATED_SYSTEM) {
            return '/';
        }
        return '/notifications';
    }

    /**
     * Queue a simple notification for one user (optional HTML email via queue).
     */
    public static function notifyUser(int $userId, string $type, string $message, ?string $subjectPrefix = null): void
    {
        $db = Database::getInstance();
        $db->prepare('INSERT INTO notifications (user_id, type, related_type, related_id, project_id, message) VALUES (?, ?, ?, ?, NULL, ?)')
            ->execute([$userId, $type, self::RELATED_SYSTEM, 0, $message]);
        $notificationId = (int) $db->lastInsertId();

        $stmt = $db->prepare('SELECT email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $email = trim((string) $stmt->fetchColumn());
        if ($email === '') {
            return;
        }
        $emailConfig = AppSettings::getEmailConfig();
        if (empty($emailConfig->enable_notification_emails)) {
            return;
        }
        $branding = AppSettings::getBrandingConfig();
        $prefix = $subjectPrefix ?: (string) ($branding->app_name ?? 'App');
        $baseUrl = defined('BASE_URL') && BASE_URL ? rtrim(BASE_URL, '/') : '';
        $clickUrl = $baseUrl . '/notifications/click/' . $notificationId;
        $body = '<!DOCTYPE html><html><body style="font-family:sans-serif"><p>' . htmlspecialchars($message) . '</p>'
            . '<p><a href="' . htmlspecialchars($clickUrl) . '">Open</a></p></body></html>';
        $db->prepare('INSERT INTO email_queue (to_email, subject, body, body_format) VALUES (?, ?, ?, ?)')
            ->execute([$email, $prefix . ': ' . $message, $body, 'html']);
    }
}
