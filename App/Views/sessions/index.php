<?php
/** @var array $sessions */

function session_os_label(string $ua): string {
    $uaLower = strtolower($ua);
    if (strpos($uaLower, 'windows nt 10.0') !== false) return 'Windows 10';
    if (strpos($uaLower, 'windows nt 11.0') !== false) return 'Windows 11';
    if (strpos($uaLower, 'windows nt 6.3') !== false) return 'Windows 8.1';
    if (strpos($uaLower, 'windows nt 6.2') !== false) return 'Windows 8';
    if (strpos($uaLower, 'windows nt 6.1') !== false) return 'Windows 7';
    if (strpos($uaLower, 'mac os x') !== false) return 'macOS';
    if (strpos($uaLower, 'android') !== false) return 'Android';
    if (strpos($uaLower, 'iphone') !== false || strpos($uaLower, 'ipad') !== false) return 'iOS';
    if (strpos($uaLower, 'linux') !== false) return 'Linux';
    return 'Unknown OS';
}

ob_start();
?>
<div class="page-header">
    <h1>Active sessions</h1>
    <p class="text-muted">
        See where you are logged in and sign out other devices or browsers.
    </p>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Your sessions</span>
        <form method="post" action="/account/sessions/logout-others" class="mb-0">
            <?php \Core\Csrf::field(); ?>
            <button type="submit" class="btn btn-sm btn-outline-danger"
                onclick="return confirm('Sign out all other devices?');">
                Sign out other devices
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if (empty($sessions)): ?>
            <p class="p-3 mb-0 text-muted">No sessions found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Device / browser</th>
                        <th>IP address</th>
                        <th>Logged in</th>
                        <th>Last active</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td>
                                <?php if ($s->user_agent): ?>
                                    <?php
                                        $os = session_os_label($s->user_agent);
                                        $uaShort = substr($s->user_agent, 0, 60);
                                    ?>
                                    <span title="<?= htmlspecialchars($s->user_agent, ENT_QUOTES, 'UTF-8') ?>">
                                        <strong><?= htmlspecialchars($os, ENT_QUOTES, 'UTF-8') ?></strong>
                                        —
                                        <?= htmlspecialchars($uaShort, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (strlen($s->user_agent) > 60): ?>…<?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s->ip_address ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s->created_at, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s->last_activity_at, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($s->is_current): ?>
                                    <span class="badge bg-primary">This device</span>
                                <?php elseif ($s->is_active): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Signed out</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($s->is_current): ?>
                                    <span class="text-muted small">Current session</span>
                                <?php elseif ($s->is_active): ?>
                                    <form method="post" action="/account/sessions/logout/<?= (int) $s->id ?>" class="d-inline">
                                        <?php \Core\Csrf::field(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Sign out this device?');">
                                            Sign out
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Ended</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Active Sessions';
$currentPage = 'account-sessions';
require __DIR__ . '/../layout/main.php';
?>
