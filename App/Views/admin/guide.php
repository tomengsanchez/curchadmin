<?php
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Admin guide</h2>
    <a href="/help" class="btn btn-outline-secondary">Help</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">What this scaffold provides</h5>
        <ul class="mb-0">
            <li>PHP front controller, router, PDO database layer, sessions, CSRF, logging</li>
            <li>Role-based capabilities (<code>App\Capabilities</code>) and admin bypass</li>
            <li>Users, roles, general / SMTP / security settings, audit log, debug log</li>
            <li>API token auth under <code>/api/auth/*</code> plus JSON endpoints you extend</li>
            <li>Migrations in <code>database/migration_*.php</code> — run via <code>php cli/migrate.php</code></li>
        </ul>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Typical setup</h5>
        <ol class="mb-0">
            <li>Copy <code>config/database-sample.php</code> to <code>config/database.php</code> and point at your DB.</li>
            <li>Run migrations; default login is <code>admin</code> (change password immediately).</li>
            <li>Set <code>config/app.php</code> <code>base_url</code> if the app lives in a subdirectory.</li>
            <li>Configure branding under <strong>System → General</strong> and SMTP if you use email.</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Building your product</h5>
        <p class="mb-0">Add capabilities in <code>App\Capabilities.php</code>, routes in <code>public/index.php</code>, controllers in <code>App\Controllers</code>, and new migrations for tables. Use <code>Capabilities::registerEntity()</code> if you load optional modules at runtime.</p>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Admin guide';
$currentPage = 'admin-guide';
require __DIR__ . '/../layout/main.php';
