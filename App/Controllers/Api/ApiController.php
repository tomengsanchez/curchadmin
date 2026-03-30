<?php
namespace App\Controllers\Api;

use Core\Controller;
use Core\Auth;
use App\NotificationService;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->requireAuthApi();
    }

    public function notifications(): void
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId <= 0) {
            $this->json([]);
            return;
        }
        $list = NotificationService::getForUser($userId, 30);
        $out = [];
        foreach ($list as $n) {
            $out[] = [
                'id' => (int) $n->id,
                'message' => $n->message ?? '',
                'created_at' => $n->created_at ?? '',
                'url' => '/notifications/click/' . (int) $n->id,
            ];
        }
        $this->json($out);
    }
}
