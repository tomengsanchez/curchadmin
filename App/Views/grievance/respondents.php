<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Grievance Respondent Profiles</h2>
    <a href="/grievance/list" class="btn btn-outline-secondary">Back to Grievances</a>
</div>

<form method="get" action="/grievance/respondents" class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-6 col-lg-4">
        <label class="form-label form-label-sm mb-1 small">Search</label>
        <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Name, mobile, email">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">Search</button>
    </div>
    <div class="col-auto">
        <a href="/grievance/respondents" class="btn btn-sm btn-outline-secondary">Clear</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Is PAPS</th>
                    <th>Gender</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th># Grievances</th>
                    <th>Latest Grievance Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($respondents ?? []) as $r): ?>
                <?php
                    $name = trim(implode(' ', array_values(array_filter([
                        $r->first_name ?? '',
                        $r->middle_name ?? '',
                        $r->last_name ?? '',
                    ], fn($v) => trim((string)$v) !== ''))));
                    if ($name === '') $name = trim((string)($r->full_name ?? ''));
                    $linkedPapsName = trim(implode(' ', array_values(array_filter([
                        $r->paps_first_name ?? '',
                        $r->paps_middle_name ?? '',
                        $r->paps_last_name ?? '',
                    ], fn($v) => trim((string)$v) !== ''))));
                    if ($linkedPapsName === '') $linkedPapsName = trim((string)($r->paps_full_name ?? ''));
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($name !== '' ? $name : '-') ?>
                        <?php if (!empty($r->profile_id) && $linkedPapsName !== ''): ?>
                            <div class="small text-muted">Linked PAPS: <a href="/profile/view/<?= (int)$r->profile_id ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($linkedPapsName) ?></a></div>
                        <?php endif; ?>
                    </td>
                    <td><?= !empty($r->is_paps) ? 'Yes' : 'No' ?></td>
                    <td><?= htmlspecialchars($r->gender ?? '-') ?></td>
                    <td><?= htmlspecialchars($r->mobile_number ?? '-') ?></td>
                    <td><?= htmlspecialchars($r->email ?? '-') ?></td>
                    <td><?= (int)($r->grievance_count ?? 0) ?></td>
                    <td><?= !empty($r->latest_grievance_date) ? htmlspecialchars(date('M j, Y H:i', strtotime($r->latest_grievance_date))) : '-' ?></td>
                    <td>
                        <a href="/grievance/list?respondent_id=<?= (int)$r->id ?>" class="btn btn-sm btn-outline-primary">View Grievances</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($respondents)): ?>
                <tr><td colspan="8" class="text-muted text-center py-4">No respondent profiles found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
    $p = $pagination ?? ['page' => 1, 'total_pages' => 1, 'per_page' => 15, 'total' => 0];
    $page = (int) ($p['page'] ?? 1);
    $totalPages = (int) ($p['total_pages'] ?? 1);
    $q = trim((string) ($search ?? ''));
    $qParam = $q !== '' ? ('&q=' . urlencode($q)) : '';
?>
<?php if ($totalPages > 1): ?>
<nav class="mt-3 d-flex justify-content-between align-items-center">
    <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?> (<?= (int)($p['total'] ?? 0) ?> total)</small>
    <div class="btn-group">
        <a class="btn btn-sm btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>" href="/grievance/respondents?page=<?= max(1, $page - 1) . $qParam ?>">Previous</a>
        <a class="btn btn-sm btn-outline-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>" href="/grievance/respondents?page=<?= min($totalPages, $page + 1) . $qParam ?>">Next</a>
    </div>
</nav>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Grievance Respondent Profiles';
$currentPage = 'grievance-respondents';
require __DIR__ . '/../layout/main.php';
?>
