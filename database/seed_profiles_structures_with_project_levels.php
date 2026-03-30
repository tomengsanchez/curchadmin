<?php
/**
 * Seeder: Profiles, Structures, Projects + Project-specific Progress Levels
 *
 * This runs the same dataset/process as:
 *   php database/seed_profiles_structures.php
 *
 * Then ensures each project has its own progress levels by copying defaults
 * (Level 1/2/3) into project scope when missing.
 *
 * Run:
 *   php database/seed_profiles_structures_with_project_levels.php
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Models\Project;
use App\Models\GrievanceProgressLevel;

/**
 * Build project-specific stage names (3-stage model) by project label/acronym.
 *
 * @return array<int,array{name:string,description:string,days:int}>
 */
function stageTemplateForProject(string $projectName): array
{
    $n = strtoupper(trim($projectName));

    // Housing / relocation-heavy programs
    if (str_contains($n, 'NHA') || str_contains($n, 'PAG-IBIG') || str_contains($n, 'BALAI') || str_contains($n, 'HLURB')) {
        return [
            ['name' => 'Intake & Housing Validation', 'description' => 'Validate claimant details and housing eligibility.', 'days' => 7],
            ['name' => 'Site/Case Assessment', 'description' => 'Coordinate with LGU/site teams and assess case evidence.', 'days' => 7],
            ['name' => 'Relocation/Closeout', 'description' => 'Implement agreed action and close case with documentation.', 'days' => 7],
        ];
    }

    // Health / social welfare
    if (str_contains($n, 'DOH') || str_contains($n, 'PHILHEALTH') || str_contains($n, 'DSWD')) {
        return [
            ['name' => 'Intake & Triage', 'description' => 'Record grievance and classify urgency.', 'days' => 7],
            ['name' => 'Validation & Coordination', 'description' => 'Verify records and coordinate with health/social focal persons.', 'days' => 7],
            ['name' => 'Action & Case Closure', 'description' => 'Deliver response and formally close the case.', 'days' => 7],
        ];
    }

    // Education / training programs
    if (str_contains($n, 'DEPED') || str_contains($n, 'TESDA') || str_contains($n, 'CHED')) {
        return [
            ['name' => 'Enrollment/Beneficiary Check', 'description' => 'Confirm learner/beneficiary and grievance details.', 'days' => 7],
            ['name' => 'School/Training Review', 'description' => 'Review with school/training administrators and validate facts.', 'days' => 7],
            ['name' => 'Resolution & Follow-through', 'description' => 'Apply resolution and confirm completion with complainant.', 'days' => 7],
        ];
    }

    // Livelihood / labor / agriculture / enterprise
    if (str_contains($n, 'SLP') || str_contains($n, 'TUPAD') || str_contains($n, 'DOLE') || str_contains($n, 'DA') || str_contains($n, 'DTI') || str_contains($n, 'BFAR') || str_contains($n, 'DAR')) {
        return [
            ['name' => 'Intake & Eligibility Review', 'description' => 'Check participant profile and grievance scope.', 'days' => 7],
            ['name' => 'Field Verification', 'description' => 'Validate case with project implementers and local coordinators.', 'days' => 7],
            ['name' => 'Program Action & Closure', 'description' => 'Implement corrective action and close grievance.', 'days' => 7],
        ];
    }

    // Default generic stages
    return [
        ['name' => 'Intake & Registration', 'description' => 'Capture grievance and acknowledge receipt.', 'days' => 7],
        ['name' => 'Assessment & Validation', 'description' => 'Assess evidence and validate with responsible units.', 'days' => 7],
        ['name' => 'Resolution & Closure', 'description' => 'Finalize action, communicate outcome, and close case.', 'days' => 7],
    ];
}

echo "Running base seeder: seed_profiles_structures.php\n";
echo "===============================================\n";
require __DIR__ . '/seed_profiles_structures.php';

echo "\nEnsuring project-specific progress levels...\n";

$projects = Project::all();
$initializedProjects = 0;
$createdLevels = 0;

foreach ($projects as $project) {
    $projectId = isset($project->id) ? (int) $project->id : 0;
    if ($projectId <= 0) {
        continue;
    }
    if (GrievanceProgressLevel::hasForProject($projectId)) {
        continue;
    }
    $projectName = (string) ($project->name ?? '');
    $stages = stageTemplateForProject($projectName);
    $inserted = 0;
    foreach ($stages as $idx => $stage) {
        GrievanceProgressLevel::create([
            'project_id' => $projectId,
            'name' => $stage['name'],
            'description' => $stage['description'],
            'sort_order' => $idx + 1,
            'days_to_address' => (int) ($stage['days'] ?? 7),
        ]);
        $inserted++;
    }
    if ($inserted > 0) {
        $initializedProjects++;
        $createdLevels += $inserted;
        echo "  Initialized project {$projectId} ({$projectName}) with custom {$inserted} stage(s).\n";
    }
}

echo "\nDone.\n";
echo "  Projects initialized with project-specific stages: {$initializedProjects}\n";
echo "  Project-specific stages created: {$createdLevels}\n";

