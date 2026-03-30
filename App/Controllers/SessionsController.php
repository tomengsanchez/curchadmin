<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\UserSession;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    public function index(): void
    {
        $sessions = UserSession::listForCurrentUser();
        $this->view('sessions/index', [
            'sessions' => $sessions,
        ]);
    }

    public function logoutOthers(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/account/sessions');
        }
        UserSession::revokeOthers();
        $this->redirect('/account/sessions');
    }

    public function logoutSession(int $id): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/account/sessions');
        }
        UserSession::revokeById($id);
        $this->redirect('/account/sessions');
    }
}

