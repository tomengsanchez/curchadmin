<?php
$canUsers = \Core\Auth::can('view_users');
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Dashboard</h2>
</div>
<p class="text-muted mb-4">Admin scaffold — add your own modules, routes, and dashboard widgets.</p>

<?php if ($canUsers): ?>
<div class="row g-3" id="mainDashboard">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Users by role</h5>
                <a href="/users" class="btn btn-sm btn-outline-secondary">Users</a>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0" id="users-by-role-list">
                    <li class="text-muted small">Loading…</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <p class="text-muted mb-0">You do not have permission to view user statistics. Use the menu to open sections you can access.</p>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

$apiUrl = (defined('BASE_URL') && BASE_URL ? BASE_URL : '') . '/api/dashboard';
$apiUrlJson = json_encode($apiUrl);

$scripts = '';
if ($canUsers) {
    $scripts = <<<HTML
<script>
(function() {
    var apiUrl = {$apiUrlJson};
    fetch(apiUrl, { credentials: "same-origin" })
        .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
        .then(function (data) {
            var u = (data && data.users) ? data.users : [];
            var ul = document.getElementById("users-by-role-list");
            if (!ul) return;
            ul.innerHTML = "";
            if (!u.length) {
                ul.innerHTML = "<li class=\\"text-muted small\\">No data.</li>";
                return;
            }
            u.forEach(function (r) {
                var li = document.createElement("li");
                li.className = "d-flex justify-content-between align-items-center py-1";
                var span = document.createElement("span");
                span.textContent = r.role || "—";
                var badge = document.createElement("span");
                badge.className = "badge bg-secondary";
                badge.textContent = (r.count || 0).toLocaleString();
                li.appendChild(span);
                li.appendChild(badge);
                ul.appendChild(li);
            });
        })
        .catch(function () {
            var ul = document.getElementById("users-by-role-list");
            if (ul) ul.innerHTML = "<li class=\\"text-muted small\\">Failed to load</li>";
        });
})();
</script>
HTML;
}

require __DIR__ . '/../layout/main.php';
