<?php
namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;
use Core\Auth;
use App\UserProjects;
use App\Models\GrievanceProgressLevel;

class GrievanceController extends Controller
{
    public function __construct()
    {
        $this->requireAuthApi();
    }

    public function dashboard(): void
    {
        if (!Auth::can('view_grievance')) {
            http_response_code(403);
            $this->json(['error' => 'Forbidden']);
            return;
        }
        $db = Database::getInstance();

        $selectedProjectId = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;
        if ($selectedProjectId < 0) {
            $selectedProjectId = 0;
        }
        $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
        $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

        // Project scoping based on user_projects (non-admin users)
        $allowedProjects = UserProjects::allowedProjectIds();
        // If a non-admin somehow sends a project_id they don't have, ignore it
        if ($selectedProjectId > 0 && $allowedProjects !== null && !in_array($selectedProjectId, $allowedProjects, true)) {
            $selectedProjectId = 0;
        }

        $baseWhere = [];
        $baseParams = [];
        if ($selectedProjectId > 0) {
            $baseWhere[] = 'project_id = ?';
            $baseParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $baseWhere[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $baseWhere[] = "project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $baseParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $baseWhere[] = 'date_recorded >= ?';
            $baseParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $baseWhere[] = 'date_recorded <= ?';
            $baseParams[] = $dateTo . ' 23:59:59';
        }
        $whereClause = empty($baseWhere) ? '' : ' WHERE ' . implode(' AND ', $baseWhere);

        // Total grievances (optionally filtered by project and date range)
        $totalSql = 'SELECT COUNT(*) FROM grievances' . $whereClause;
        $stmt = $db->prepare($totalSql);
        $stmt->execute($baseParams);
        $total = (int) $stmt->fetchColumn();

        // Recent grievances list (optionally filtered by project, user scope, and date range)
        $recentSql = 'SELECT g.id, g.grievance_case_number, g.date_recorded, g.status, g.progress_level,
            COALESCE(NULLIF(TRIM(CONCAT_WS(" ", p.first_name, p.middle_name, p.last_name)), ""), p.full_name, g.respondent_full_name) as respondent_name
            FROM grievances g
            LEFT JOIN profiles p ON p.id = g.profile_id';
        $recentParams = [];
        $recentWhere = [];
        if ($selectedProjectId > 0) {
            $recentWhere[] = 'g.project_id = ?';
            $recentParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $recentWhere[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $recentWhere[] = "g.project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $recentParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $recentWhere[] = 'g.date_recorded >= ?';
            $recentParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $recentWhere[] = 'g.date_recorded <= ?';
            $recentParams[] = $dateTo . ' 23:59:59';
        }
        if (!empty($recentWhere)) {
            $recentSql .= ' WHERE ' . implode(' AND ', $recentWhere);
        }
        $recentSql .= ' ORDER BY g.id DESC LIMIT 10';
        $stmt = $db->prepare($recentSql);
        $stmt->execute($recentParams);
        $recent = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Status breakdown (optionally filtered by project and date range)
        $statusSql = 'SELECT status, COUNT(*) as cnt FROM grievances' . $whereClause . ' GROUP BY status';
        $stmt = $db->prepare($statusSql);
        $stmt->execute($baseParams);
        $statusBreakdown = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Monthly trend and this/last month counts (honors project/user scope and date range)
        $trendWhere = [];
        $trendParams = [];
        if ($selectedProjectId > 0) {
            $trendWhere[] = 'project_id = ?';
            $trendParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $trendWhere[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $trendWhere[] = "project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $trendParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $trendWhere[] = 'date_recorded >= ?';
            $trendParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $trendWhere[] = 'date_recorded <= ?';
            $trendParams[] = $dateTo . ' 23:59:59';
        }
        $trendWhereClause = empty($trendWhere) ? '' : ' WHERE ' . implode(' AND ', $trendWhere);

        $trendSql = '
            SELECT DATE_FORMAT(date_recorded, "%Y-%m") AS ym, COUNT(*) AS cnt
            FROM grievances
            ' . $trendWhereClause . '
            GROUP BY ym ORDER BY ym
        ';
        $stmt = $db->prepare($trendSql);
        $stmt->execute($trendParams);
        $trendByKey = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $row) {
            $key = (string) ($row->ym ?? '');
            if ($key === '') {
                continue;
            }
            $trendByKey[$key] = (int) ($row->cnt ?? 0);
        }

        // Build monthly trend array: use date range if set, else last 12 months
        $monthlyTrend = [];
        if ($dateFrom !== '' && $dateTo !== '') {
            $start = new \DateTime($dateFrom);
            $end = new \DateTime($dateTo);
            $iter = clone $start;
            $iter->modify('first day of this month');
            while ($iter <= $end) {
                $key = $iter->format('Y-m');
                $monthlyTrend[] = [
                    'month' => $key,
                    'label' => $iter->format('M Y'),
                    'count' => $trendByKey[$key] ?? 0,
                ];
                $iter->modify('+1 month');
            }
        } else {
            for ($i = 11; $i >= 0; $i--) {
                $key = date('Y-m', strtotime("-$i months"));
                $monthlyTrend[] = [
                    'month' => $key,
                    'label' => date('M Y', strtotime("-$i months")),
                    'count' => $trendByKey[$key] ?? 0,
                ];
            }
        }

        $currentKey = date('Y-m');
        $lastKey = date('Y-m', strtotime('-1 month'));
        $thisMonth = $trendByKey[$currentKey] ?? 0;
        $lastMonth = $trendByKey[$lastKey] ?? 0;

        // By project breakdown (honors selected project filter, user scope, and date range)
        $byProjectSql = '
            SELECT proj.name as project_name, COUNT(*) as cnt
            FROM grievances g
            LEFT JOIN projects proj ON proj.id = g.project_id
            ' . $whereClause . '
            GROUP BY g.project_id, proj.name
            ORDER BY cnt DESC
            LIMIT 8
        ';
        $stmt = $db->prepare($byProjectSql);
        $stmt->execute($baseParams);
        $byProject = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // In-progress by stage (optionally filtered by project, user scope, and date range)
        $inProgressWhere = ['g.status = \'in_progress\''];
        $inProgressParams = [];
        if ($selectedProjectId > 0) {
            $inProgressWhere[] = 'g.project_id = ?';
            $inProgressParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $inProgressWhere[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $inProgressWhere[] = "g.project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $inProgressParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $inProgressWhere[] = 'g.date_recorded >= ?';
            $inProgressParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $inProgressWhere[] = 'g.date_recorded <= ?';
            $inProgressParams[] = $dateTo . ' 23:59:59';
        }
        $inProgressSql = "
            SELECT g.project_id,
                   g.progress_level,
                   COALESCE(pl.name, CONCAT('Level ', g.progress_level)) as level_name,
                   proj.name AS project_name,
                   COUNT(*) as cnt
            FROM grievances g
            LEFT JOIN grievance_progress_levels pl ON pl.id = g.progress_level
            LEFT JOIN projects proj ON proj.id = g.project_id
            WHERE " . implode(' AND ', $inProgressWhere) . "
            GROUP BY g.project_id, g.progress_level, pl.name, proj.name
            ORDER BY proj.name, g.progress_level
        ";
        $stmt = $db->prepare($inProgressSql);
        $stmt->execute($inProgressParams);
        $inProgressRaw = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $inProgressLevels = [];
        foreach ($inProgressRaw as $row) {
            $projectName = trim((string) ($row->project_name ?? ''));
            $levelName = trim((string) ($row->level_name ?? ''));
            $displayName = $levelName !== '' ? $levelName : '—';
            if ($selectedProjectId <= 0 && $projectName !== '') {
                $displayName = $projectName . ' - ' . $displayName;
            }
            $inProgressLevels[] = (object) [
                'project_id' => (int) ($row->project_id ?? 0),
                'progress_level' => (int) ($row->progress_level ?? 0),
                'level_name' => $levelName,
                'display_level_name' => $displayName,
                'project_name' => $projectName,
                'cnt' => (int) ($row->cnt ?? 0),
            ];
        }

        // Count grievances that have exceeded days_to_address based on when they
        // entered the current in-progress level (not the original date_recorded). Uses DevClock for testing.
        $needsEscalationWhere = [
            "g.status = 'in_progress'",
            "pl.days_to_address IS NOT NULL",
            "pl.days_to_address > 0",
            "DATEDIFF(
                ?,
                DATE((
                    SELECT MIN(s1.created_at)
                    FROM grievance_status_log s1
                    WHERE s1.grievance_id = g.id
                      AND s1.status = 'in_progress'
                      AND s1.progress_level = g.progress_level
                      AND NOT EXISTS (
                          SELECT 1
                          FROM grievance_status_log s2
                          WHERE s2.grievance_id = s1.grievance_id
                            AND (
                                s2.created_at > s1.created_at
                                OR (s2.created_at = s1.created_at AND s2.id > s1.id)
                            )
                            AND (
                                s2.status <> 'in_progress'
                                OR COALESCE(s2.progress_level, 0) <> COALESCE(s1.progress_level, 0)
                            )
                      )
                ))
            ) > pl.days_to_address",
        ];
        $needsEscalationParams = [\App\DevClock::today()];
        if ($selectedProjectId > 0) {
            $needsEscalationWhere[] = 'g.project_id = ?';
            $needsEscalationParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $needsEscalationWhere[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $needsEscalationWhere[] = "g.project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $needsEscalationParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $needsEscalationWhere[] = 'g.date_recorded >= ?';
            $needsEscalationParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $needsEscalationWhere[] = 'g.date_recorded <= ?';
            $needsEscalationParams[] = $dateTo . ' 23:59:59';
        }
        $needsEscalationSql = "
            SELECT g.project_id,
                   g.progress_level,
                   COALESCE(pl.name, CONCAT('Level ', g.progress_level)) AS level_name,
                   proj.name AS project_name,
                   COUNT(*) as cnt
            FROM grievances g
            JOIN grievance_progress_levels pl ON pl.id = g.progress_level
            LEFT JOIN projects proj ON proj.id = g.project_id
            WHERE " . implode(' AND ', $needsEscalationWhere) . "
            GROUP BY g.project_id, g.progress_level, pl.name, proj.name
        ";
        $stmt = $db->prepare($needsEscalationSql);
        $stmt->execute($needsEscalationParams);
        $needsEscalationRaw = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $needsEscalationByLevel = [];
        $needsEscalationRows = [];
        $levelsByProject = [];
        $isLastByProjectAndLevel = [];
        foreach ($needsEscalationRaw as $row) {
            $projectId = (int) ($row->project_id ?? 0);
            $levelId = (int) ($row->progress_level ?? 0);
            if ($levelId <= 0) {
                continue;
            }
            $count = (int) ($row->cnt ?? 0);
            $needsEscalationByLevel[$levelId] = ($needsEscalationByLevel[$levelId] ?? 0) + $count;

            if (!isset($levelsByProject[$projectId])) {
                $levelsByProject[$projectId] = GrievanceProgressLevel::forProjectOrDefault($projectId);
                $lastLevelId = null;
                if (!empty($levelsByProject[$projectId])) {
                    $ordered = $levelsByProject[$projectId];
                    usort($ordered, function ($a, $b) {
                        $sa = (int) ($a->sort_order ?? 0);
                        $sb = (int) ($b->sort_order ?? 0);
                        if ($sa === $sb) {
                            return (int) ($a->id ?? 0) <=> (int) ($b->id ?? 0);
                        }
                        return $sa <=> $sb;
                    });
                    $last = end($ordered);
                    $lastLevelId = (int) ($last->id ?? 0);
                }
                foreach ($levelsByProject[$projectId] as $pl) {
                    $plId = (int) ($pl->id ?? 0);
                    if ($plId > 0) {
                        $isLastByProjectAndLevel[$projectId . '_' . $plId] = ($lastLevelId > 0 && $plId === $lastLevelId);
                    }
                }
            }

            $projectName = trim((string) ($row->project_name ?? ''));
            $levelName = trim((string) ($row->level_name ?? ''));
            $displayName = $levelName !== '' ? $levelName : '—';
            if ($selectedProjectId <= 0 && $projectName !== '') {
                $displayName = $projectName . ' - ' . $displayName;
            }
            $isLast = !empty($isLastByProjectAndLevel[$projectId . '_' . $levelId]);
            $needsEscalationRows[] = [
                'project_id' => $projectId,
                'progress_level' => $levelId,
                'project_name' => $projectName,
                'level_name' => $levelName,
                'display_level_name' => $displayName,
                'count' => $count,
                'action' => $isLast ? 'close' : 'escalate',
            ];
        }

        // By category of grievance (JSON array column, optionally filtered by project, user scope, and date range)
        $byCategoryOn = ["JSON_CONTAINS(g.grievance_category_ids, CAST(c.id AS CHAR), '$')"];
        $byCategoryParams = [];
        if ($selectedProjectId > 0) {
            $byCategoryOn[] = "g.project_id = ?";
            $byCategoryParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $byCategoryOn[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $byCategoryOn[] = "g.project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $byCategoryParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $byCategoryOn[] = "g.date_recorded >= ?";
            $byCategoryParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $byCategoryOn[] = "g.date_recorded <= ?";
            $byCategoryParams[] = $dateTo . ' 23:59:59';
        }
        $byCategorySql = "
            SELECT c.id, c.name, COUNT(g.id) AS cnt
            FROM grievance_categories c
            LEFT JOIN grievances g ON " . implode(' AND ', $byCategoryOn) . "
            GROUP BY c.id, c.name
            ORDER BY cnt DESC, c.sort_order, c.name
        ";
        $stmt = $db->prepare($byCategorySql);
        $stmt->execute($byCategoryParams);
        $byCategory = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // By type of grievance (JSON array column, optionally filtered by project, user scope, and date range)
        $byTypeOn = ["JSON_CONTAINS(g.grievance_type_ids, CAST(t.id AS CHAR), '$')"];
        $byTypeParams = [];
        if ($selectedProjectId > 0) {
            $byTypeOn[] = "g.project_id = ?";
            $byTypeParams[] = $selectedProjectId;
        } elseif ($allowedProjects !== null) {
            if (empty($allowedProjects)) {
                $byTypeOn[] = '1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($allowedProjects), '?'));
                $byTypeOn[] = "g.project_id IN ($placeholders)";
                foreach ($allowedProjects as $pid) {
                    $byTypeParams[] = $pid;
                }
            }
        }
        if ($dateFrom !== '') {
            $byTypeOn[] = "g.date_recorded >= ?";
            $byTypeParams[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $byTypeOn[] = "g.date_recorded <= ?";
            $byTypeParams[] = $dateTo . ' 23:59:59';
        }
        $byTypeSql = "
            SELECT t.id, t.name, COUNT(g.id) AS cnt
            FROM grievance_types t
            LEFT JOIN grievances g ON " . implode(' AND ', $byTypeOn) . "
            GROUP BY t.id, t.name
            ORDER BY cnt DESC, t.sort_order, t.name
        ";
        $stmt = $db->prepare($byTypeSql);
        $stmt->execute($byTypeParams);
        $byType = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $this->json([
            'totalGrievances'        => $total,
            'recentGrievances'       => $recent,
            'statusBreakdown'        => $statusBreakdown,
            'thisMonth'              => $thisMonth,
            'lastMonth'              => $lastMonth,
            'byProject'              => $byProject,
            'monthlyTrend'           => $monthlyTrend,
            'byCategory'             => $byCategory,
            'byType'                 => $byType,
            'inProgressLevels'       => $inProgressLevels,
            'needsEscalationByLevel' => $needsEscalationByLevel,
            'needsEscalationRows'    => $needsEscalationRows,
        ]);
    }
}

