#!/usr/bin/env php
<?php
/**
 * Re-map grievance progress levels from default IDs to project-specific IDs.
 *
 * Usage:
 *   php cli/remap_progress_levels.php
 *   php cli/remap_progress_levels.php --project=3
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

require_once dirname(__DIR__) . '/bootstrap.php';

$projectId = 0;
foreach (($argv ?? []) as $arg) {
    if (strpos($arg, '--project=') === 0) {
        $projectId = (int) substr($arg, 10);
    }
}

$total = 0;
$projectCounts = [];

if ($projectId > 0) {
    $count = \App\Models\GrievanceProgressLevel::remapProjectLevelReferences($projectId);
    $projectCounts[$projectId] = $count;
    $total += $count;
} else {
    $projects = \App\Models\Project::all();
    foreach ($projects as $project) {
        $pid = isset($project->id) ? (int) $project->id : 0;
        if ($pid <= 0) {
            continue;
        }
        $count = \App\Models\GrievanceProgressLevel::remapProjectLevelReferences($pid);
        $projectCounts[$pid] = $count;
        $total += $count;
    }
}

foreach ($projectCounts as $pid => $count) {
    if ($count > 0) {
        echo "Project {$pid}: remapped {$count} row(s)\n";
    }
}
echo "Total remapped rows: {$total}\n";

