<?php
namespace App\Models;

use Core\Database;

class GrievanceProgressLevel
{
    protected static string $table = 'grievance_progress_levels';

    protected static function db(): \PDO
    {
        return Database::getInstance();
    }

    public static function all(): array
    {
        self::ensureDefaultLevels();
        $stmt = self::db()->query('
            SELECT pl.*, p.name AS project_name
            FROM grievance_progress_levels pl
            LEFT JOIN projects p ON p.id = pl.project_id
            ORDER BY (pl.project_id IS NULL) ASC, p.name ASC, pl.sort_order ASC, pl.name ASC
        ');
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /** Return all levels scoped to a project, or defaults (Level 1/2/3) when none exist. */
    public static function forProjectOrDefault(?int $projectId): array
    {
        self::ensureDefaultLevels();
        $projectId = (int) ($projectId ?? 0);
        if ($projectId > 0) {
            $rows = self::forProject($projectId);
            if (!empty($rows)) {
                return $rows;
            }
        }
        return self::defaults();
    }

    public static function forProject(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }
        $stmt = self::db()->prepare('
            SELECT pl.*, p.name AS project_name
            FROM grievance_progress_levels pl
            LEFT JOIN projects p ON p.id = pl.project_id
            WHERE pl.project_id = ?
            ORDER BY pl.sort_order, pl.name
        ');
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public static function hasForProject(int $projectId): bool
    {
        if ($projectId <= 0) {
            return false;
        }
        $stmt = self::db()->prepare('SELECT 1 FROM grievance_progress_levels WHERE project_id = ? LIMIT 1');
        $stmt->execute([$projectId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Copy default levels into a project scope once; no-op if project already has levels. */
    public static function initializeProjectFromDefaults(int $projectId): int
    {
        if ($projectId <= 0 || self::hasForProject($projectId)) {
            return 0;
        }

        $defaults = self::defaults();
        if (empty($defaults)) {
            return 0;
        }

        $db = self::db();
        $inserted = 0;
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('
                INSERT INTO grievance_progress_levels (project_id, name, description, sort_order, days_to_address)
                VALUES (?, ?, ?, ?, ?)
            ');
            foreach ($defaults as $level) {
                $stmt->execute([
                    $projectId,
                    trim((string) ($level->name ?? '')),
                    trim((string) ($level->description ?? '')),
                    (int) ($level->sort_order ?? 0),
                    isset($level->days_to_address) && $level->days_to_address !== null ? (int) $level->days_to_address : null,
                ]);
                $inserted++;
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return 0;
        }
        self::remapProjectLevelReferences($projectId);
        return $inserted;
    }

    /**
     * Remap project grievance/status-log progress levels from default IDs
     * to project-specific IDs using sort_order first, then name fallback.
     *
     * @return int Updated rows across grievances and grievance_status_log.
     */
    public static function remapProjectLevelReferences(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $defaults = self::defaults();
        $projectLevels = self::forProject($projectId);
        if (empty($defaults) || empty($projectLevels)) {
            return 0;
        }

        $projectBySort = [];
        $projectByName = [];
        foreach ($projectLevels as $pl) {
            $pid = (int) ($pl->id ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $sort = (int) ($pl->sort_order ?? 0);
            if (!isset($projectBySort[$sort])) {
                $projectBySort[$sort] = $pid;
            }
            $nm = mb_strtolower(trim((string) ($pl->name ?? '')));
            if ($nm !== '' && !isset($projectByName[$nm])) {
                $projectByName[$nm] = $pid;
            }
        }

        $idMap = [];
        foreach ($defaults as $def) {
            $fromId = (int) ($def->id ?? 0);
            if ($fromId <= 0) {
                continue;
            }
            $target = null;
            $sort = (int) ($def->sort_order ?? 0);
            if (isset($projectBySort[$sort])) {
                $target = $projectBySort[$sort];
            } else {
                $nm = mb_strtolower(trim((string) ($def->name ?? '')));
                if ($nm !== '' && isset($projectByName[$nm])) {
                    $target = $projectByName[$nm];
                }
            }
            if ($target !== null && $target > 0 && $target !== $fromId) {
                $idMap[$fromId] = $target;
            }
        }

        if (empty($idMap)) {
            return 0;
        }

        $db = self::db();
        $updated = 0;
        $db->beginTransaction();
        try {
            $stmtGrievance = $db->prepare('
                UPDATE grievances
                SET progress_level = ?
                WHERE project_id = ? AND progress_level = ?
            ');
            $stmtLog = $db->prepare('
                UPDATE grievance_status_log l
                JOIN grievances g ON g.id = l.grievance_id
                SET l.progress_level = ?
                WHERE g.project_id = ? AND l.progress_level = ?
            ');
            foreach ($idMap as $fromId => $toId) {
                $stmtGrievance->execute([$toId, $projectId, $fromId]);
                $updated += (int) $stmtGrievance->rowCount();
                $stmtLog->execute([$toId, $projectId, $fromId]);
                $updated += (int) $stmtLog->rowCount();
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return 0;
        }

        return $updated;
    }

    public static function defaults(): array
    {
        self::ensureDefaultLevels();
        $stmt = self::db()->query("
            SELECT pl.*, NULL AS project_name
            FROM grievance_progress_levels pl
            WHERE pl.project_id IS NULL
            ORDER BY pl.sort_order, pl.name
        ");
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public static function findForProjectOrDefault(int $id, ?int $projectId): ?object
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }
        $projectId = (int) ($projectId ?? 0);
        $levels = self::forProjectOrDefault($projectId);
        foreach ($levels as $level) {
            if ((int) ($level->id ?? 0) === $id) {
                return $level;
            }
        }
        return null;
    }

    public static function find(int $id): ?object
    {
        $stmt = self::db()->prepare('
            SELECT pl.*, p.name AS project_name
            FROM grievance_progress_levels pl
            LEFT JOIN projects p ON p.id = pl.project_id
            WHERE pl.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_OBJ) ?: null;
    }

    public static function create(array $data): int
    {
        $db = self::db();
        $stmt = $db->prepare('INSERT INTO grievance_progress_levels (project_id, name, description, sort_order, days_to_address) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            isset($data['project_id']) && (int) $data['project_id'] > 0 ? (int) $data['project_id'] : null,
            trim($data['name'] ?? ''),
            trim($data['description'] ?? ''),
            (int) ($data['sort_order'] ?? 0),
            isset($data['days_to_address']) && $data['days_to_address'] !== '' ? (int) $data['days_to_address'] : null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::db()->prepare('UPDATE grievance_progress_levels SET project_id = ?, name = ?, description = ?, sort_order = ?, days_to_address = ? WHERE id = ?');
        $stmt->execute([
            isset($data['project_id']) && (int) $data['project_id'] > 0 ? (int) $data['project_id'] : null,
            trim($data['name'] ?? ''),
            trim($data['description'] ?? ''),
            (int) ($data['sort_order'] ?? 0),
            isset($data['days_to_address']) && $data['days_to_address'] !== '' ? (int) $data['days_to_address'] : null,
            $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare('DELETE FROM grievance_progress_levels WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    private static function ensureDefaultLevels(): void
    {
        $db = self::db();
        $stmt = $db->query('SELECT COUNT(*) FROM grievance_progress_levels WHERE project_id IS NULL');
        $count = (int) $stmt->fetchColumn();
        if ($count > 0) {
            return;
        }

        $db->beginTransaction();
        try {
            $insert = $db->prepare('
                INSERT INTO grievance_progress_levels (project_id, name, description, sort_order, days_to_address)
                VALUES (?, ?, ?, ?, ?)
            ');
            $insert->execute([null, 'Level 1', '', 1, null]);
            $insert->execute([null, 'Level 2', '', 2, null]);
            $insert->execute([null, 'Level 3', '', 3, null]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }
    }
}
