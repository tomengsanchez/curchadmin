<?php
namespace App\Controllers\Api;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Structure;
use App\Models\GrievanceProgressLevel;
use App\UserProjects;
use App\NotificationService;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->requireAuthApi();
    }

    public function projects(): void
    {
        if (!Auth::canAny(['view_projects', 'add_projects', 'edit_projects', 'view_profiles', 'add_profiles', 'edit_profiles', 'view_grievance', 'add_grievance', 'edit_grievance', 'view_users', 'add_users', 'edit_users'])) {
            $this->json([]);
            return;
        }
        $q = $_GET['q'] ?? '';
        $db = Database::getInstance();
        $term = '%' . $q . '%';

        $allowed = UserProjects::allowedProjectIds();
        if ($allowed !== null && empty($allowed)) {
            $this->json([]);
            return;
        }

        $params = [$term];
        $where = 'name LIKE ?';
        if ($allowed !== null) {
            $placeholders = implode(',', array_fill(0, count($allowed), '?'));
            $where .= " AND id IN ($placeholders)";
            foreach ($allowed as $pid) {
                $params[] = $pid;
            }
        }

        $sql = "SELECT id, name FROM projects WHERE $where ORDER BY name LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($rows);
    }

    public function projectUsers(int $projectId): void
    {
        if (!Auth::canAny(['view_users', 'view_projects'])) {
            http_response_code(403);
            $this->json(['error' => 'Forbidden']);
            return;
        }

        $allowed = UserProjects::allowedProjectIds();
        if ($allowed !== null) {
            if (empty($allowed) || !in_array($projectId, $allowed, true)) {
                $this->json([]);
                return;
            }
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT u.id, u.username, u.display_name, u.email, r.name AS role_name
            FROM user_projects up
            INNER JOIN users u ON u.id = up.user_id
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE up.project_id = ?
            ORDER BY (u.display_name IS NULL OR u.display_name = \'\'), u.display_name, u.username
        ');
        $stmt->execute([$projectId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($rows);
    }

    public function userProjects(int $userId): void
    {
        if (!Auth::canAny(['view_users', 'view_projects'])) {
            http_response_code(403);
            $this->json(['error' => 'Forbidden']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT p.id, p.name
            FROM user_projects up
            INNER JOIN projects p ON p.id = up.project_id
            WHERE up.user_id = ?
            ORDER BY p.name
        ');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($rows);
    }

    public function profiles(): void
    {
        if (!Auth::canAny(['view_structure', 'add_structure', 'edit_structure', 'view_profiles', 'view_grievance', 'add_grievance', 'edit_grievance'])) {
            $this->json([]);
            return;
        }
        $q = trim($_GET['q'] ?? '');
        $db = Database::getInstance();
        $search = '%' . $q . '%';

        $allowed = UserProjects::allowedProjectIds();
        if ($allowed !== null && empty($allowed)) {
            $this->json([]);
            return;
        }

        $params = [$q, $search, $search, $search];
        $projectFilter = '';
        if ($allowed !== null) {
            $placeholders = implode(',', array_fill(0, count($allowed), '?'));
            $projectFilter = " AND p.project_id IN ($placeholders)";
            foreach ($allowed as $pid) {
                $params[] = $pid;
            }
        }

        $sql = '
            SELECT p.id, COALESCE(NULLIF(TRIM(CONCAT_WS(" ", p.first_name, p.middle_name, p.last_name)), ""), NULLIF(p.full_name,""), p.papsid, "") as name,
                   p.project_id, proj.name as project_name
            FROM profiles p
            LEFT JOIN projects proj ON proj.id = p.project_id
            WHERE (? = "" OR TRIM(CONCAT_WS(" ", p.first_name, p.middle_name, p.last_name)) LIKE ? OR p.full_name LIKE ? OR p.papsid LIKE ?)' . $projectFilter . '
            ORDER BY COALESCE(NULLIF(TRIM(CONCAT_WS(" ", p.first_name, p.middle_name, p.last_name)), ""), NULLIF(p.full_name,""), p.papsid)
            LIMIT 20';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($rows);
    }

    public function profileStructures(int $profileId): void
    {
        if (!Auth::canAny(['view_structure', 'view_profiles', 'edit_profiles'])) {
            http_response_code(403);
            $this->json(['error' => 'Forbidden']);
            return;
        }
        $structures = Structure::byOwner($profileId);
        $out = [];
        foreach ($structures as $s) {
            $out[] = [
                'id' => $s->id,
                'strid' => $s->strid,
                'structure_tag' => $s->structure_tag,
                'description' => $s->description,
                'other_details' => $s->other_details,
                'tagging_images' => $s->tagging_images,
                'structure_images' => $s->structure_images,
            ];
        }
        $this->json($out);
    }

    public function notifications(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->json([]);
            return;
        }
        $list = NotificationService::getForUser($userId, 30);
        $out = [];
        foreach ($list as $n) {
            $out[] = [
                'id' => (int) $n->id,
                'type' => $n->type,
                'related_type' => $n->related_type,
                'related_id' => (int) $n->related_id,
                'message' => $n->message ?? '',
                'created_at' => $n->created_at ?? '',
                'url' => '/notifications/click/' . (int) $n->id,
            ];
        }
        $this->json($out);
    }

    /** Autocomplete source for non-PAPS respondent first names from past grievances. */
    public function respondentFirstNames(): void
    {
        if (!Auth::canAny(['view_grievance', 'add_grievance', 'edit_grievance'])) {
            $this->json([]);
            return;
        }
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $term = '%' . $q . '%';
        [$projectFilter, $projectParams] = $this->allowedProjectFilterSql('g.project_id');
        $sql = "SELECT DISTINCT TRIM(r.first_name) AS name
                FROM grievances g
                INNER JOIN grievance_respondents r ON r.id = g.respondent_id
                WHERE r.is_paps = 0
                  AND TRIM(COALESCE(r.first_name, '')) <> ''
                  AND TRIM(r.first_name) LIKE ?
                  $projectFilter
                ORDER BY name
                LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge([$term], $projectParams));
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $this->json(array_values(array_filter(array_map(fn($v) => trim((string)$v), $rows))));
    }

    /** Autocomplete source for middle names filtered by first name. */
    public function respondentMiddleNames(): void
    {
        if (!Auth::canAny(['view_grievance', 'add_grievance', 'edit_grievance'])) {
            $this->json([]);
            return;
        }
        $firstName = trim((string) ($_GET['first_name'] ?? ''));
        if ($firstName === '') {
            $this->json([]);
            return;
        }
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $term = '%' . $q . '%';
        [$projectFilter, $projectParams] = $this->allowedProjectFilterSql('g.project_id');
        $sql = "SELECT DISTINCT TRIM(r.middle_name) AS name
                FROM grievances g
                INNER JOIN grievance_respondents r ON r.id = g.respondent_id
                WHERE r.is_paps = 0
                  AND TRIM(COALESCE(r.first_name, '')) = ?
                  AND TRIM(COALESCE(r.middle_name, '')) <> ''
                  AND TRIM(r.middle_name) LIKE ?
                  $projectFilter
                ORDER BY name
                LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge([$firstName, $term], $projectParams));
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $this->json(array_values(array_filter(array_map(fn($v) => trim((string)$v), $rows))));
    }

    /** Autocomplete source for last names filtered by first/middle name. */
    public function respondentLastNames(): void
    {
        if (!Auth::canAny(['view_grievance', 'add_grievance', 'edit_grievance'])) {
            $this->json([]);
            return;
        }
        $firstName = trim((string) ($_GET['first_name'] ?? ''));
        if ($firstName === '') {
            $this->json([]);
            return;
        }
        $middleName = trim((string) ($_GET['middle_name'] ?? ''));
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $term = '%' . $q . '%';
        [$projectFilter, $projectParams] = $this->allowedProjectFilterSql('g.project_id');
        $middleWhere = $middleName === ''
            ? "AND TRIM(COALESCE(r.middle_name, '')) = ''"
            : "AND TRIM(COALESCE(r.middle_name, '')) = ?";
        $sql = "SELECT DISTINCT TRIM(r.last_name) AS name
                FROM grievances g
                INNER JOIN grievance_respondents r ON r.id = g.respondent_id
                WHERE r.is_paps = 0
                  AND TRIM(COALESCE(r.first_name, '')) = ?
                  $middleWhere
                  AND TRIM(COALESCE(r.last_name, '')) <> ''
                  AND TRIM(r.last_name) LIKE ?
                  $projectFilter
                ORDER BY name
                LIMIT 20";
        $params = [$firstName];
        if ($middleName !== '') {
            $params[] = $middleName;
        }
        $params[] = $term;
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($params, $projectParams));
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $this->json(array_values(array_filter(array_map(fn($v) => trim((string)$v), $rows))));
    }

    /** List prior non-PAPS grievances for a matched respondent name. */
    public function respondentHistory(): void
    {
        if (!Auth::canAny(['view_grievance', 'add_grievance', 'edit_grievance'])) {
            $this->json([]);
            return;
        }
        $firstName = trim((string) ($_GET['first_name'] ?? ''));
        $middleName = trim((string) ($_GET['middle_name'] ?? ''));
        $lastName = trim((string) ($_GET['last_name'] ?? ''));
        if ($firstName === '') {
            $this->json([]);
            return;
        }

        $db = Database::getInstance();
        [$projectFilter, $projectParams] = $this->allowedProjectFilterSql('g.project_id');

        $sql = "SELECT
                    g.id,
                    g.date_recorded,
                    g.status,
                    g.project_id,
                    g.progress_level,
                    pl.name AS progress_level_name,
                    (
                        SELECT GROUP_CONCAT(DISTINCT gt.name ORDER BY gt.sort_order, gt.name SEPARATOR ', ')
                        FROM grievance_types gt
                        WHERE JSON_CONTAINS(g.grievance_type_ids, CAST(gt.id AS CHAR), '$')
                    ) AS grievance_type_names,
                    (
                        SELECT GROUP_CONCAT(DISTINCT gc.name ORDER BY gc.sort_order, gc.name SEPARATOR ', ')
                        FROM grievance_categories gc
                        WHERE JSON_CONTAINS(g.grievance_category_ids, CAST(gc.id AS CHAR), '$')
                    ) AS grievance_category_names
                FROM grievances g
                INNER JOIN grievance_respondents r ON r.id = g.respondent_id
                LEFT JOIN grievance_progress_levels pl ON pl.id = g.progress_level
                WHERE r.is_paps = 0
                  AND TRIM(COALESCE(r.first_name, '')) = ?
                  AND (? = '' OR TRIM(COALESCE(r.middle_name, '')) = ?)
                  AND (? = '' OR TRIM(COALESCE(r.last_name, '')) = ?)
                  $projectFilter
                ORDER BY g.date_recorded DESC, g.id DESC
                LIMIT 20";
        $params = [$firstName, $middleName, $middleName, $lastName, $lastName];
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($params, $projectParams));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $levelNameCache = []; // "projectId_levelId" => name
        $levelsByProject = []; // projectId => [levelId => name]
        $out = [];
        foreach ($rows as $r) {
            $projectId = (int) ($r['project_id'] ?? 0);
            $levelId = (int) ($r['progress_level'] ?? 0);
            $progressName = trim((string) ($r['progress_level_name'] ?? ''));
            if ($levelId > 0 && $progressName === '') {
                $cacheKey = $projectId . '_' . $levelId;
                if (!isset($levelNameCache[$cacheKey])) {
                    if (!isset($levelsByProject[$projectId])) {
                        $levelsByProject[$projectId] = [];
                        $levels = GrievanceProgressLevel::forProjectOrDefault($projectId);
                        foreach ($levels as $pl) {
                            $pid = (int) ($pl->id ?? 0);
                            if ($pid > 0) {
                                $levelsByProject[$projectId][$pid] = trim((string) ($pl->name ?? ''));
                            }
                        }
                    }
                    $levelNameCache[$cacheKey] = $levelsByProject[$projectId][$levelId] ?? ('Level ' . $levelId);
                }
                $progressName = $levelNameCache[$cacheKey];
            }
            $out[] = [
                'id' => (int) ($r['id'] ?? 0),
                'date_recorded' => $r['date_recorded'] ?? null,
                'grievance_type' => trim((string) ($r['grievance_type_names'] ?? '')),
                'category' => trim((string) ($r['grievance_category_names'] ?? '')),
                'status' => $r['status'] ?? '',
                'progress' => $progressName,
            ];
        }
        $this->json($out);
    }

    /** Get latest non-PAPS grievance details for matched respondent name. */
    public function respondentLatestDetails(): void
    {
        if (!Auth::canAny(['view_grievance', 'add_grievance', 'edit_grievance'])) {
            $this->json(['matched' => false]);
            return;
        }
        $firstName = trim((string) ($_GET['first_name'] ?? ''));
        $middleName = trim((string) ($_GET['middle_name'] ?? ''));
        $lastName = trim((string) ($_GET['last_name'] ?? ''));
        if ($firstName === '' || $lastName === '') {
            $this->json(['matched' => false]);
            return;
        }

        $db = Database::getInstance();
        [$projectFilter, $projectParams] = $this->allowedProjectFilterSql('g.project_id');
        $sql = "SELECT
                    g.id,
                    g.project_id,
                    proj.name AS project_name,
                    COALESCE(r.gender, g.gender) AS gender,
                    COALESCE(r.gender_specify, g.gender_specify) AS gender_specify,
                    COALESCE(r.valid_id_philippines, g.valid_id_philippines) AS valid_id_philippines,
                    COALESCE(r.id_number, g.id_number) AS id_number,
                    COALESCE(r.vulnerability_ids, g.vulnerability_ids) AS vulnerability_ids,
                    COALESCE(r.respondent_type_ids, g.respondent_type_ids) AS respondent_type_ids,
                    COALESCE(r.respondent_type_other_specify, g.respondent_type_other_specify) AS respondent_type_other_specify,
                    COALESCE(r.home_business_address, g.home_business_address) AS home_business_address,
                    COALESCE(r.mobile_number, g.mobile_number) AS mobile_number,
                    COALESCE(r.email, g.email) AS email,
                    COALESCE(r.contact_others_specify, g.contact_others_specify) AS contact_others_specify
                FROM grievances g
                INNER JOIN grievance_respondents r ON r.id = g.respondent_id
                LEFT JOIN projects proj ON proj.id = g.project_id
                WHERE r.is_paps = 0
                  AND TRIM(COALESCE(r.first_name, '')) = ?
                  AND (? = '' OR TRIM(COALESCE(r.middle_name, '')) = ?)
                  AND TRIM(COALESCE(r.last_name, '')) = ?
                  $projectFilter
                ORDER BY g.date_recorded DESC, g.id DESC
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge([$firstName, $middleName, $middleName, $lastName], $projectParams));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $this->json(['matched' => false]);
            return;
        }
        $this->json([
            'matched' => true,
            'id' => (int) ($row['id'] ?? 0),
            'project_id' => (int) ($row['project_id'] ?? 0),
            'project_name' => trim((string) ($row['project_name'] ?? '')),
            'gender' => trim((string) ($row['gender'] ?? '')),
            'gender_specify' => trim((string) ($row['gender_specify'] ?? '')),
            'valid_id_philippines' => trim((string) ($row['valid_id_philippines'] ?? '')),
            'id_number' => trim((string) ($row['id_number'] ?? '')),
            'vulnerability_ids' => $this->parseJsonIntArray($row['vulnerability_ids'] ?? null),
            'respondent_type_ids' => $this->parseJsonIntArray($row['respondent_type_ids'] ?? null),
            'respondent_type_other_specify' => trim((string) ($row['respondent_type_other_specify'] ?? '')),
            'home_business_address' => trim((string) ($row['home_business_address'] ?? '')),
            'mobile_number' => trim((string) ($row['mobile_number'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')),
            'contact_others_specify' => trim((string) ($row['contact_others_specify'] ?? '')),
        ]);
    }

    /**
     * Project scope SQL fragment + bind params for grievances alias.
     * Returns ['', []] for admins/unscoped users.
     */
    private function allowedProjectFilterSql(string $column = 'project_id'): array
    {
        $allowed = UserProjects::allowedProjectIds();
        if ($allowed === null) {
            return ['', []];
        }
        if (empty($allowed)) {
            return [' AND 1=0', []];
        }
        $placeholders = implode(',', array_fill(0, count($allowed), '?'));
        return [" AND {$column} IN ({$placeholders})", array_values($allowed)];
    }

    /** Parse JSON array field into integer ids. */
    private function parseJsonIntArray($raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        return array_values(array_map('intval', array_filter($decoded, fn($v) => (int)$v > 0)));
    }
}

