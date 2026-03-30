<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$db = Core\Database::getInstance();

$hasTable = function (string $table) use ($db): bool {
    $stmt = $db->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);
    return (bool) $stmt->fetch(\PDO::FETCH_NUM);
};

$db->exec('SET FOREIGN_KEY_CHECKS = 0');
$db->exec('TRUNCATE TABLE notifications');
$db->exec('TRUNCATE TABLE audit_log');
$db->exec('TRUNCATE TABLE grievance_attachments');
$db->exec('TRUNCATE TABLE grievance_status_log');
$db->exec('TRUNCATE TABLE grievances');
$db->exec('TRUNCATE TABLE grievance_respondents');
if ($hasTable('grievance_progress_levels')) {
    $db->exec('TRUNCATE TABLE grievance_progress_levels');
}
$db->exec('TRUNCATE TABLE structures');
$db->exec('TRUNCATE TABLE profiles');
$db->exec('TRUNCATE TABLE projects');
$db->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "Truncated: notifications, audit_log, grievance_attachments, grievance_status_log, grievances, grievance_respondents, grievance_progress_levels, structures, profiles, projects\n";
