<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>In Progress Stages</h2>
    <a href="/grievance/options/progress-levels/create<?= !empty($selectedProjectId) ? ('?project_id=' . (int)$selectedProjectId) : '' ?>" class="btn btn-primary">Add Stage</a>
</div>
<div class="d-flex align-items-center gap-2 mb-2 small text-muted">
    <span class="badge bg-info text-dark" data-bs-toggle="tooltip" title="This stage is configured only for a specific project.">Project-specific stage</span>
    <span class="badge bg-secondary" data-bs-toggle="tooltip" title="Fallback stage used when a project has no custom stage setup yet.">Default stage</span>
</div>
<form method="get" action="/grievance/options/progress-levels" class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-4">
        <label class="form-label form-label-sm mb-1 small">Project scope</label>
        <select name="project_id" class="form-select form-select-sm">
            <option value="0" <?= empty($selectedProjectId) ? 'selected' : '' ?>>All scopes</option>
            <?php foreach (($projects ?? []) as $project): ?>
            <option value="<?= (int)$project->id ?>" <?= (int)($selectedProjectId ?? 0) === (int)$project->id ? 'selected' : '' ?>><?= htmlspecialchars($project->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-auto">
        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
        <a href="/grievance/options/progress-levels" class="btn btn-sm btn-outline-secondary">Clear</a>
    </div>
</form>
<?php if (!empty($selectedProjectId)): ?>
    <?php if (empty($projectHasOwnLevels)): ?>
        <div class="alert alert-info py-2">
            <strong><?= htmlspecialchars($selectedProject->name ?? ('Project #' . (int)$selectedProjectId)) ?></strong>
            is currently using default stages (`Level 1`, `Level 2`, `Level 3`) because no project-specific stages are set yet.
            <form method="post" action="/grievance/options/progress-levels/initialize-project" class="d-inline ms-2">
                <?= \Core\Csrf::field() ?>
                <input type="hidden" name="project_id" value="<?= (int)$selectedProjectId ?>">
                <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Create project-specific stages (Level 1, Level 2, Level 3) for this project?');">Initialize this project</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary py-2">
            Showing project-specific stages for <strong><?= htmlspecialchars($selectedProject->name ?? ('Project #' . (int)$selectedProjectId)) ?></strong>.
            <form method="post" action="/grievance/options/progress-levels/remap-project" class="d-inline ms-2">
                <?= \Core\Csrf::field() ?>
                <input type="hidden" name="project_id" value="<?= (int)$selectedProjectId ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Re-map existing grievance records in this project to the current project-specific stages by stage order?');">Re-map existing records</button>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Scope</th><th>Name</th><th>Sort Order</th><th>Days to Address</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($items ?? [] as $i): ?>
                <tr>
                    <td>
                        <?php if (!empty($i->project_id)): ?>
                            <span class="badge bg-info text-dark" data-bs-toggle="tooltip" title="Project-specific stage"><?= htmlspecialchars($i->project_name ?? ('Project #' . (int)$i->project_id)) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary" data-bs-toggle="tooltip" title="Default stage">Default stage</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($i->name) ?></td>
                    <td><?= (int)$i->sort_order ?></td>
                    <td><?= isset($i->days_to_address) && $i->days_to_address !== null ? (int)$i->days_to_address : '' ?></td>
                    <td><?= htmlspecialchars(mb_substr($i->description ?? '', 0, 80)) ?><?= mb_strlen($i->description ?? '') > 80 ? '...' : '' ?></td>
                    <td><a href="/grievance/options/progress-levels/edit/<?= (int)$i->id ?>" class="btn btn-sm btn-outline-primary">Edit</a> <form method="post" action="/grievance/options/progress-levels/delete/<?= (int)$i->id ?>" class="d-inline" onsubmit="return confirm('Delete this stage? Grievances using it may show an unknown level.');"><?= \Core\Csrf::field() ?><button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?><tr><td colspan="6" class="text-muted text-center py-4">No stages yet. Add Level 1, Level 2, Level 3 or custom stages.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="mt-2"><a href="/grievance">← Back to Grievance</a></p>
<?php
$content = ob_get_clean();
$pageTitle = 'In Progress Stages';
$currentPage = 'grievance-progress-levels';
$scripts = ($scripts ?? '') . '<script>
document.addEventListener("DOMContentLoaded", function () {
    if (window.bootstrap && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=\"tooltip\"]"));
        tooltipTriggerList.forEach(function (el) { new bootstrap.Tooltip(el); });
    }
});
</script>';
require __DIR__ . '/../../layout/main.php';
?>
