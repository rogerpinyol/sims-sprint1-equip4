<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

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
            header('Location: /login');
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
        $this->render(__DIR__ . '/../views/user/profile.php', compact('user','errors','success'));
    }

    public function updateProfile(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
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
}
