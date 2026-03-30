<?php
namespace App\Controllers\Api;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\AuditLog;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->requireAuthApi();
    }

    public function index(): void
    {
        $entityType = $_GET['entity_type'] ?? '';
        $entityId = isset($_GET['entity_id']) ? (int) $_GET['entity_id'] : 0;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 20;

        if ($entityType !== 'user' || $entityId <= 0) {
            http_response_code(400);
            $this->json(['error' => 'Invalid parameters. Use entity_type=user and entity_id.']);
            return;
        }

        if (!Auth::can('view_users')) {
            http_response_code(403);
            $this->json(['error' => 'Forbidden']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$entityId]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            $this->json(['error' => 'Entity not found.']);
            return;
        }

        $pageData = AuditLog::forPaginated('user', $entityId, $page, $perPage);

        $this->json([
            'items' => $pageData['items'],
            'page' => $pageData['page'],
            'per_page' => $pageData['per_page'],
            'has_more' => $pageData['has_more'],
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'current_user_id' => (int) (Auth::id() ?? 0),
        ]);
    }
}
