<?php
namespace App\Controllers;

use Core\Controller;

class HelpController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    public function index(): void
    {
        $from = isset($_GET['from']) ? (string) $_GET['from'] : '';
        $module = $this->resolveModuleKey($from);
        $this->view('help/index', [
            'module' => $module,
            'from' => $from,
        ]);
    }

    private function resolveModuleKey(string $from): string
    {
        if ($from === '') {
            return 'general';
        }

        return match ($from) {
            'dashboard' => 'dashboard',
            'notifications' => 'notifications',
            'general' => 'system-general',
            'email-settings' => 'email-settings',
            'security-settings' => 'security-settings',
            'settings' => 'settings',
            'users', 'user-roles' => 'user-management',
            'audit-trail' => 'audit-trail',
            'debug-log' => 'debug-log',
            'development' => 'development',
            'account' => 'account',
            'account-sessions' => 'account-sessions',
            'admin-guide' => 'admin-guide',
            default => 'general',
        };
    }
}
