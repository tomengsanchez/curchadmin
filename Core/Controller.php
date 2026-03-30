<?php
namespace Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = dirname(__DIR__) . "/App/Views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View not found: {$view}");
        }
    }

    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    protected function json($data): void
    {
        header('Content-Type: application/json');
        $payload = $data;
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        if (is_string($requestPath) && strpos($requestPath, '/api/') === 0) {
            $payload = $this->normalizeApiEnvelope($data);
        }
        echo json_encode($payload);
        exit;
    }

    /**
     * Normalize API responses to a consistent envelope:
     * { success: bool, data: mixed|null, error: { code, message, details? }|null }
     */
    private function normalizeApiEnvelope($data): array
    {
        if (is_array($data) && array_key_exists('success', $data)) {
            // Already in envelope-like form.
            $hasData = array_key_exists('data', $data);
            $hasError = array_key_exists('error', $data);
            return [
                'success' => (bool) $data['success'],
                'data' => $hasData ? $data['data'] : null,
                'error' => $hasError ? $data['error'] : null,
            ];
        }

        if (is_array($data) && array_key_exists('error', $data)) {
            $errorValue = $data['error'];
            $message = is_string($errorValue)
                ? $errorValue
                : (is_array($errorValue) ? (string) ($errorValue['message'] ?? 'Request failed') : 'Request failed');
            $code = 'API_ERROR';
            $details = null;
            if (is_array($errorValue)) {
                $code = (string) ($errorValue['code'] ?? $code);
                if (array_key_exists('details', $errorValue)) {
                    $details = $errorValue['details'];
                }
            }
            if (isset($data['message']) && is_string($data['message']) && trim($data['message']) !== '') {
                $message = $data['message'];
            }
            $error = ['code' => $code, 'message' => $message];
            if ($details !== null) {
                $error['details'] = $details;
            }
            return [
                'success' => false,
                'data' => null,
                'error' => $error,
            ];
        }

        return [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];
    }

    protected function auth(): ?object
    {
        return Auth::user();
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    /** API variant: return 401 JSON instead of redirect. Use for REST endpoints. */
    protected function requireAuthApi(): bool
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized', 'message' => 'Authentication required']);
            return false;
        }
        return true;
    }

    protected function requireCapability(string $capability): void
    {
        $this->requireAuth();
        if (!Auth::can($capability)) {
            $this->redirect('/');
        }
    }

    /** Validate CSRF for POST requests. Call at the start of any action that accepts POST. Redirects with error if invalid. */
    protected function validateCsrf(): void
    {
        if (!\Core\Csrf::validate()) {
            $this->redirect($this->csrfRedirectUrl(), 403);
        }
    }

    /** Override in subclass to set redirect target when CSRF validation fails (default /). */
    protected function csrfRedirectUrl(): string
    {
        return '/?error=csrf';
    }
}
