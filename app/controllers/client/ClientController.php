<?php
// Correct relative paths (this file is in app/controllers/client)
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';

class ClientController extends Controller
{
    private ?User $users = null;
    private int $tenantId = 0;

    public function __construct()
    {
        parent::__construct();
        $this->tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        if ($this->tenantId > 0) {
            $this->users = new User($this->tenantId);
        }
    }

    public function profile(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /client/login');
            exit;
        }
        $this->requireTenant();
        if (!$this->users) {
            http_response_code(500);
            echo 'User model unavailable';
            return;
        }
        $user = $this->users->getById((int)$_SESSION['user_id']);
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $success = !empty($_SESSION['flash_success']);
        unset($_SESSION['flash_success']);
    $this->render(__DIR__ . '/../../views/client/profile.php', compact('user','errors','success'));
    }

    public function updateProfile(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /client/login');
            exit;
        }
        $this->requireTenant();
        if (!$this->users) {
            $_SESSION['flash_errors'] = ['User model not available.'];
            header('Location: /profile');
            exit;
        }
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'accessibility_flags' => $_POST['accessibility_flags'] ?? null,
        ];
        try {
            $this->users->updateDetails((int)$_SESSION['user_id'], $data);
            $_SESSION['flash_success'] = true;
        } catch (\Throwable $e) {
            $_SESSION['flash_errors'] = [$e->getMessage()];
        }
        header('Location: /profile');
        exit;
    }

    // POST /profile/delete
    public function deleteAccount(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
        $this->requireTenant();
        if (!$this->users) {
            $_SESSION['flash_errors'] = ['User model not available.'];
            header('Location: /profile');
            exit;
        }
        $uid = (int)$_SESSION['user_id'];
        // Prevent deletion of non-client roles (defensive)
        $user = $this->users->getById($uid);
        if (!$user) {
            $_SESSION = [];
            session_destroy();
            header('Location: /');
            exit;
        }
        if (!in_array($user['role'] ?? 'client', ['client'], true)) {
            $_SESSION['flash_errors'] = ['Solo cuentas de cliente pueden eliminarse.'];
            header('Location: /profile');
            exit;
        }
        $ok = $this->users->delete($uid);
        // Destroy session regardless for privacy if deletion attempted
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        // If deletion failed for some reason, still logout but pass a flag via query (optional)
        header('Location: /auth/login' . ($ok ? '' : '?deleted=0'));
        exit;
    }
}
