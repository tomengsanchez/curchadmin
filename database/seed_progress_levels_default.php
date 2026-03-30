<?php
/**
 * Seeder: Default In Progress Stages (Level 1/2/3)
 *
 * This seeds only the fallback/default stages used when a project
 * has no project-specific progress levels configured.
 *
 * Run:
 *   php database/seed_progress_levels_default.php
 */

require_once __DIR__ . '/../bootstrap.php';

use Core\Database;

$db = Database::getInstance();

function tableHasColumn(\PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);
    return (bool) $stmt->fetch(\PDO::FETCH_ASSOC);
}

echo "Default Progress Levels Seeder\n";
echo "==============================\n";

$hasProjectId = tableHasColumn($db, 'grievance_progress_levels', 'project_id');
$defaults = [
    ['Level 1', '', 1, null],
    ['Level 2', '', 2, null],
    ['Level 3', '', 3, null],
];

$inserted = 0;
foreach ($defaults as [$name, $description, $sortOrder, $days]) {
    if ($hasProjectId) {
        $check = $db->prepare('
            SELECT id
            FROM grievance_progress_levels
            WHERE project_id IS NULL AND name = ?
            LIMIT 1
        ');
        $check->execute([$name]);
        if ($check->fetch(\PDO::FETCH_ASSOC)) {
            echo "  Skipping {$name} (already exists as default)\n";
            continue;
        }
        $ins = $db->prepare('
            INSERT INTO grievance_progress_levels (project_id, name, description, sort_order, days_to_address)
            VALUES (NULL, ?, ?, ?, ?)
        ');
        $ins->execute([$name, $description, $sortOrder, $days]);
    } else {
        $check = $db->prepare('
            SELECT id
            FROM grievance_progress_levels
            WHERE name = ?
            LIMIT 1
        ');
        $check->execute([$name]);
        if ($check->fetch(\PDO::FETCH_ASSOC)) {
            echo "  Skipping {$name} (already exists)\n";
            continue;
        }
        $ins = $db->prepare('
            INSERT INTO grievance_progress_levels (name, description, sort_order, days_to_address)
            VALUES (?, ?, ?, ?)
        ');
        $ins->execute([$name, $description, $sortOrder, $days]);
    }
    $inserted++;
    echo "  Added {$name}\n";
}

echo "\nDone.\n";
echo "Inserted default progress levels: {$inserted}\n";

