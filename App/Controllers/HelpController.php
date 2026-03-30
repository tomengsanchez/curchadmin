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
        $from = isset($_GET['from']) ? (string)$_GET['from'] : '';
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

        switch ($from) {
            case 'dashboard':
                return 'dashboard';
            case 'profile':
                return 'profile';
            case 'structure':
                return 'structure';
            case 'library':
                return 'library';
            case 'notifications':
                return 'notifications';
            case 'general':
                return 'system-general';
            case 'email-settings':
                return 'email-settings';
            case 'security-settings':
                return 'security-settings';
            case 'settings':
                return 'settings';
            case 'users':
            case 'user-roles':
                return 'user-management';
            case 'grievance-dashboard':
                return 'grievance-dashboard';
            case 'grievance-respondents':
                return 'grievance-respondents';
            case 'grievance':
            case 'grievance-list':
            case 'grievance-vulnerabilities':
            case 'grievance-respondent-types':
            case 'grievance-grm-channels':
            case 'grievance-preferred-languages':
            case 'grievance-types':
            case 'grievance-categories':
            case 'grievance-progress-levels':
                return 'grievance';
            case 'audit-trail':
                return 'audit-trail';
            case 'debug-log':
                return 'debug-log';
            case 'development':
                return 'development';
            case 'account':
                return 'account';
            case 'account-sessions':
                return 'account-sessions';
            case 'admin-guide':
                return 'admin-guide';
            default:
                return 'general';
        }
    }
}

