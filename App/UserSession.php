<?php
namespace App;

use Core\Auth;
use Core\Database;

class UserSession
{
    private static function db(): \PDO
    {
        return Database::getInstance();
    }

    private static function currentSessionId(): ?string
    {
        $sid = session_id();
        return $sid !== '' ? $sid : null;
    }

    private static function clientIp(): ?string
    {
        // Reuse LoginThrottle logic? For now, basic REMOTE_ADDR (no proxy handling).
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
    * Called on successful login to register the current browser/device session.
    */
    public static function onLogin(int $userId): void
    {
        $sid = self::currentSessionId();
        if ($sid === null) {
            return;
        }

        $db = self::db();
        $stmt = $db->prepare('SELECT id FROM user_sessions WHERE user_id = ? AND session_id = ? LIMIT 1');
        $stmt->execute([$userId, $sid]);
        $existingId = $stmt->fetchColumn();

        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
        $ip = substr((string) (self::clientIp() ?? ''), 0, 45);

        if ($existingId) {
            $upd = $db->prepare('UPDATE user_sessions SET user_agent = ?, ip_address = ?, last_activity_at = NOW(), revoked_at = NULL WHERE id = ?');
            $upd->execute([$ua, $ip, $existingId]);
        } else {
            $ins = $db->prepare('INSERT INTO user_sessions (user_id, session_id, user_agent, ip_address, created_at, last_activity_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
            $ins->execute([$userId, $sid, $ua, $ip]);
        }
    }

    /**
    * Called on each authenticated request to:
    * - ensure a session row exists
    * - update last_activity_at
    * - force logout if revoked.
    */
    public static function touchOrEnforceForCurrent(): void
    {
        $userId = Auth::id();
        $sid = self::currentSessionId();
        if ($userId === null || $sid === null) {
            return;
        }

        $db = self::db();
        $stmt = $db->prepare('SELECT id, revoked_at FROM user_sessions WHERE user_id = ? AND session_id = ? LIMIT 1');
        $stmt->execute([$userId, $sid]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($row && $row->revoked_at !== null) {
            // This session has been revoked from another device/action.
            Auth::logout();
            header('Location: /login?logged_out=1');
            exit;
        }

        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
        $ip = substr((string) (self::clientIp() ?? ''), 0, 45);

        if ($row) {
            $upd = $db->prepare('UPDATE user_sessions SET last_activity_at = NOW(), user_agent = ?, ip_address = ? WHERE id = ?');
            $upd->execute([$ua, $ip, $row->id]);
        } else {
            $ins = $db->prepare('INSERT INTO user_sessions (user_id, session_id, user_agent, ip_address, created_at, last_activity_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
            $ins->execute([$userId, $sid, $ua, $ip]);
        }
    }

    /**
    * Mark the current browser/device session as revoked (used on logout).
    */
    public static function revokeCurrent(): void
    {
        $userId = Auth::id();
        $sid = self::currentSessionId();
        if ($userId === null || $sid === null) {
            return;
        }
        $db = self::db();
        $stmt = $db->prepare('UPDATE user_sessions SET revoked_at = NOW() WHERE user_id = ? AND session_id = ? AND revoked_at IS NULL');
        $stmt->execute([$userId, $sid]);
    }

    /**
    * Revoke all other active sessions for the current user (leave this one).
    */
    public static function revokeOthers(): void
    {
        $userId = Auth::id();
        $sid = self::currentSessionId();
        if ($userId === null) {
            return;
        }
        $db = self::db();
        if ($sid === null) {
            $stmt = $db->prepare('UPDATE user_sessions SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL');
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare('UPDATE user_sessions SET revoked_at = NOW() WHERE user_id = ? AND session_id <> ? AND revoked_at IS NULL');
            $stmt->execute([$userId, $sid]);
        }
    }

    /**
    * Revoke a specific session (by ID) owned by current user.
    */
    public static function revokeById(int $sessionId): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            return;
        }
        $db = self::db();
        $stmt = $db->prepare('UPDATE user_sessions SET revoked_at = NOW() WHERE id = ? AND user_id = ? AND revoked_at IS NULL');
        $stmt->execute([$sessionId, $userId]);
    }

    /**
    * List sessions for current user for the UI.
    *
    * @return array<int,object>
    */
    public static function listForCurrentUser(): array
    {
        $userId = Auth::id();
        if ($userId === null) {
            return [];
        }
        $sid = self::currentSessionId();

        $db = self::db();
        $stmt = $db->prepare('
            SELECT id, session_id, user_agent, ip_address, created_at, last_activity_at, revoked_at
            FROM user_sessions
            WHERE user_id = ?
            ORDER BY revoked_at IS NULL DESC, last_activity_at DESC, created_at DESC
        ');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        foreach ($rows as $row) {
            $row->is_current = ($sid !== null && $row->session_id === $sid);
            $row->is_active = ($row->revoked_at === null);
        }

        return $rows;
    }
}

