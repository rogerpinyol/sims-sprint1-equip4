<?php
// Simple router for testing tenants feature (super admin dashboard)

require_once __DIR__ . '/../app/controllers/TenantController.php';

// Start session and simulate super_admin for testing
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user'] = ['id' => 1, 'role' => 'super_admin'];

// Basic routing by path
$path = $_SERVER['REQUEST_URI'];

// Handle root and /index.php
if ($path === '/' || $path === '/index.php') {
    // Show a simple welcome page for all users
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Welcome - EcoMotion Platform</title>
</head>
<body style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px;'>
    <h1>Welcome to EcoMotion Platform</h1>
    <p>This is a multi-tenant SaaS platform for vehicle fleet management.</p>
    <ul>
        <li><a href='/index.php/admin/tenants'>Super Admin Dashboard (Tenants Management)</a></li>
    </ul>
</body>
</html>";
    exit;
}
$controller = new TenantController();

// List tenants (dashboard)
if (
    $path === '/admin/tenants' ||
    $path === '/admin/tenants/' ||
    $path === '/index.php/admin/tenants' ||
    $path === '/index.php/admin/tenants/' ||
    preg_match('#^/admin/tenants\?#', $path) ||
    preg_match('#^/index\.php/admin/tenants\?#', $path)
) {
    try {
        $queryParams = $_GET ?? [];
        $response = $controller->index($queryParams);
        $tenants = $response['body']['data'] ?? [];
        $pagination = $response['body']['pagination'] ?? [];
        // Ensure CSRF token exists for forms
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        // Show feedback if available
        $feedback = $_SESSION['tenant_create_feedback'] ?? null;
        if ($feedback) {
            unset($_SESSION['tenant_create_feedback']);
        }
        // Fix: API key feedback compatibility
        if (isset($feedback['api_key']) && empty($feedback['api_key']) && isset($feedback['data']['api_key'])) {
            $feedback['api_key'] = $feedback['data']['api_key'];
        }
        include __DIR__ . '/../app/views/admin/tenants.php';
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        include __DIR__ . '/../app/views/errors/error.php';
    }
    exit;
}

// Show tenant details
if (preg_match('#^/admin/tenants/show\.php#', $path) && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $response = $controller->show($id);
        $tenant = $response['body']['data'] ?? null;
        if (!$tenant) throw new Exception('Tenant not found');
        include __DIR__ . '/../app/views/admin/tenant_show.php';
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        include __DIR__ . '/../app/views/errors/error.php';
    }
    exit;
}

// Create tenant (POST)
if ($path === '/admin/tenants/create.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CSRF check
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }
        $payload = $_POST;
        $response = $controller->store($payload);
        // Store feedback in session and redirect to dashboard
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 201,
            'message' => $response['body']['message'] ?? ($response['status'] === 201 ? 'Tenant created successfully.' : 'Error creating tenant.'),
            'api_key' => $response['body']['api_key'] ?? null,
            'data' => $response['body']['data'] ?? null
        ];
    } catch (Exception $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    header('Location: /admin/tenants');
    exit;
}

// Edit tenant (show form)
if (preg_match('#^/admin/tenants/edit\.php#', $path) && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $response = $controller->show($id);
        $tenant = $response['body']['data'] ?? null;
        if (!$tenant) throw new Exception('Tenant not found');
        include __DIR__ . '/../app/views/admin/tenant_edit.php';
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        include __DIR__ . '/../app/views/errors/error.php';
    }
    exit;
}

// Update tenant (POST)
if ($path === '/admin/tenants/update.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->update($id, $_POST);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'Tenant updated.'
        ];
    } catch (Exception $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    header('Location: /admin/tenants');
    exit;
}

// Deactivate tenant (POST)
if ($path === '/admin/tenants/deactivate.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deactivate($id);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'Tenant deactivated.'
        ];
    } catch (Exception $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    header('Location: /admin/tenants');
    exit;
}

// Activate tenant (POST)
if ($path === '/admin/tenants/activate.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->activate($id);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'Tenant activated.'
        ];
    } catch (Exception $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    header('Location: /admin/tenants');
    exit;
}

// Rotate API key (POST)
if ($path === '/admin/tenants/rotate_api_key.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->rotateApiKey($id);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'API key rotated.',
            'api_key' => $response['body']['api_key'] ?? null
        ];
    } catch (Exception $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    header('Location: /admin/tenants');
    exit;
}

// Default: 404
http_response_code(404);
echo "404 Not Found";