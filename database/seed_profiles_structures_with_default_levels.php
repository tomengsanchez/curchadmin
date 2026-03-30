<?php
/**
 * Seeder: Profiles, Structures, Projects + Default Progress Levels Only
 *
 * This runs the same dataset/process as:
 *   php database/seed_profiles_structures.php
 *
 * Then ensures fallback/default progress levels (Level 1/2/3) exist.
 * It does NOT create project-specific progress levels.
 *
 * Run:
 *   php database/seed_profiles_structures_with_default_levels.php
 */

require_once __DIR__ . '/../bootstrap.php';

echo "Running base seeder: seed_profiles_structures.php\n";
echo "===============================================\n";
require __DIR__ . '/seed_profiles_structures.php';

echo "\nEnsuring default progress levels (no project-specific setup)...\n";
require __DIR__ . '/seed_progress_levels_default.php';

echo "\nDone.\n";
echo "Note: This seeder keeps progress levels as default-only.\n";

