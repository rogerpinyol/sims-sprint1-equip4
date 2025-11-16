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
        $this->requireClientAuth();
        $tenantId = $this->requireTenant();
        $model = $this->ensureUserModel($tenantId);
        $user = $model->getById((int)$_SESSION['user_id']);
        [$errors, $success] = $this->consumeFlashes();
        $this->renderProfile($user, $errors, $success);
    }

    public function updateProfile(): void
    {
        $this->requireClientAuth();
        $tenantId = $this->requireTenant();
        $model = $this->ensureUserModel($tenantId);
        $data = $this->collectProfileInput();
        $this->attemptUpdateProfile($model, (int)$_SESSION['user_id'], $data);
        $this->redirect('/profile');
    }

    // POST /profile/delete
    public function deleteAccount(): void
    {
        $this->requireClientAuth();
        $tenantId = $this->requireTenant();
        $model = $this->ensureUserModel($tenantId);
        $uid = (int)$_SESSION['user_id'];
        $user = $model->getById($uid);
        if (!$user) {
            $this->logoutAndRedirect('/');
            return;
        }
        if (!$this->canDeleteClient($user)) {
            $_SESSION['flash_errors'] = ['Only client accounts can be deleted.'];
            $this->redirect('/profile');
            return;
        }
        $ok = $model->delete($uid);
        $this->logoutAndRedirect('/auth/login' . ($ok ? '' : '?deleted=0'));
    }

    // ---- Helpers ----
    private function requireClientAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /client/login');
            exit;
        }
    }

    private function ensureUserModel(int $tenantId): User
    {
        if (!$this->users) {
            $this->users = new User($tenantId);
        }
        return $this->users;
    }

    private function consumeFlashes(): array
    {
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $success = !empty($_SESSION['flash_success']);
        unset($_SESSION['flash_success']);
        return [$errors, $success];
    }

    private function renderProfile(array $user, array $errors, bool $success): void
    {
        $this->render(__DIR__ . '/../../views/client/profile.php', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success,
            'layout' => __DIR__ . '/../../views/layouts/app.php',
            'title' => 'Edit Profile',
        ]);
    }

    private function collectProfileInput(): array
    {
        return [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'accessibility_flags' => $_POST['accessibility_flags'] ?? null,
        ];
    }

    private function attemptUpdateProfile(User $model, int $userId, array $data): void
    {
        try {
            $model->updateDetails($userId, $data);
            $_SESSION['flash_success'] = true;
        } catch (\Throwable $e) {
            $_SESSION['flash_errors'] = [$e->getMessage()];
        }
    }

    private function canDeleteClient(array $user): bool
    {
        return in_array($user['role'] ?? 'client', ['client'], true);
    }

    private function logoutAndRedirect(string $location): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        header('Location: ' . $location);
        exit;
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
