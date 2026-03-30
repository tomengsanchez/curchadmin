<?php
$module = $module ?? 'general';
$from = $from ?? '';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Help</h2>
    <a href="/admin-guide" class="btn btn-outline-secondary">Admin guide</a>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Using this application</h5>
        <p class="text-muted">This is a reusable <strong>admin scaffold</strong>: authentication, roles, capabilities, users, settings, email, security, audit trail, development tools, and a JSON API. Add your own modules by registering routes, controllers, models, and capabilities.</p>

        <?php if ($module === 'dashboard'): ?>
        <p class="mb-0"><strong>Dashboard</strong> shows a short summary (e.g. users by role). Extend <code>App\Controllers\Api\DashboardController</code> and the dashboard view for your product.</p>
        <?php elseif ($module === 'user-management'): ?>
        <p class="mb-0"><strong>User management</strong> — create users, assign roles, and edit capabilities under <em>User Roles &amp; Capabilities</em>. Administrators bypass capability checks.</p>
        <?php elseif ($module === 'settings'): ?>
        <p class="mb-0"><strong>Settings</strong> — UI theme and layout. Notification email toggles are defined in code when you add features.</p>
        <?php elseif ($module === 'system-general'): ?>
        <p class="mb-0"><strong>System → General</strong> — branding (app name, company, logo) and regional defaults.</p>
        <?php elseif ($module === 'email-settings'): ?>
        <p class="mb-0"><strong>SMTP</strong> — outbound mail for 2FA and queued notifications. Use <em>Test email</em> after saving.</p>
        <?php elseif ($module === 'security-settings'): ?>
        <p class="mb-0"><strong>Security</strong> — optional email 2FA, session timeout, login throttling, and password policy.</p>
        <?php elseif ($module === 'audit-trail'): ?>
        <p class="mb-0"><strong>Audit trail</strong> — filter by entity type, date, and acting user. Entity links appear when a route exists for that type.</p>
        <?php elseif ($module === 'development'): ?>
        <p class="mb-0"><strong>Development</strong> — simulated clock and request diagnostics (admin only).</p>
        <?php elseif ($module === 'account-sessions'): ?>
        <p class="mb-0"><strong>Active sessions</strong> — devices where you are signed in; you can revoke others.</p>
        <?php else: ?>
        <p class="mb-0">Use the sidebar to open <strong>Settings</strong>, <strong>System</strong>, and <strong>User Management</strong>. For operators, see the <a href="/admin-guide">Admin guide</a>.</p>
        <?php endif; ?>

        <?php if ($from !== ''): ?>
        <p class="small text-muted mt-3 mb-0">Context: <code><?= htmlspecialchars($from) ?></code></p>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Help';
$currentPage = 'help';
require __DIR__ . '/../layout/main.php';
