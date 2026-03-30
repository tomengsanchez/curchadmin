<?php
namespace App\Controllers\Api;

use Core\Controller;
use Core\Auth;
use App\AuditLog;
use App\Models\Profile;
use App\Models\Structure;
use App\Models\Grievance;

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

        if (!in_array($entityType, ['profile', 'structure', 'grievance'], true) || $entityId <= 0) {
            http_response_code(400);
            $this->json(['error' => 'Invalid parameters.']);
            return;
        }

        // Permission + project-scope checks by loading the entity using existing models.
        switch ($entityType) {
            case 'profile':
                if (!Auth::can('view_profiles')) {
                    http_response_code(403);
                    $this->json(['error' => 'Forbidden']);
                    return;
                }
                $entity = Profile::find($entityId);
                break;
            case 'structure':
                if (!Auth::can('view_structure')) {
                    http_response_code(403);
                    $this->json(['error' => 'Forbidden']);
                    return;
                }
                $entity = Structure::find($entityId);
                break;
            case 'grievance':
                if (!Auth::can('view_grievance')) {
                    http_response_code(403);
                    $this->json(['error' => 'Forbidden']);
                    return;
                }
                $entity = Grievance::find($entityId);
                break;
            default:
                $entity = null;
        }

        if (!$entity) {
            http_response_code(404);
            $this->json(['error' => 'Entity not found.']);
            return;
        }

        $pageData = AuditLog::forPaginated($entityType, $entityId, $page, $perPage);

        $this->json([
            'items'    => $pageData['items'],
            'page'     => $pageData['page'],
            'per_page' => $pageData['per_page'],
            'has_more' => $pageData['has_more'],
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'current_user_id' => (int) (Auth::id() ?? 0),
        ]);
    }
}

