<?php
// Admin routes module (super admin tenants management)
// Expects $router, $requireSuperAdmin, $csrfCheck, $renderError, $ensureCsrfToken available

if (isset($__routeContext) && is_array($__routeContext)) {
    $router = $__routeContext['router'] ?? $router ?? null;
    $requireSuperAdmin = $__routeContext['requireSuperAdmin'] ?? $requireSuperAdmin ?? null;
    $csrfCheck = $__routeContext['csrfCheck'] ?? $csrfCheck ?? null;
    $renderError = $__routeContext['renderError'] ?? $renderError ?? null;
    $ensureCsrfToken = $__routeContext['ensureCsrfToken'] ?? $ensureCsrfToken ?? null;
}

// GET: Tenants dashboard
$router->add('GET', '/admin/tenants', function () use ($renderError, $ensureCsrfToken) {
    try {
        $controller = new TenantController();
        $response = $controller->index($_GET ?? []);
        $tenants = $response['body']['data'] ?? [];
        $pagination = $response['body']['pagination'] ?? [];
        // Ensure CSRF token exists for forms
        if (is_callable($ensureCsrfToken)) $ensureCsrfToken();
        // Flash feedback
        $feedback = $_SESSION['tenant_create_feedback'] ?? null;
        if ($feedback) unset($_SESSION['tenant_create_feedback']);
        if (isset($feedback['api_key']) && empty($feedback['api_key']) && isset($feedback['data']['api_key'])) {
            $feedback['api_key'] = $feedback['data']['api_key'];
        }
        include __DIR__ . '/../app/views/admin/tenants.php';
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (is_callable($renderError)) $renderError($msg, 500); else echo $msg;
    }
}, [$requireSuperAdmin]);

// GET: Show tenant details (keeps legacy path with query param)
$router->add('GET', '/admin/tenants/show.php', function () use ($renderError) {
    try {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception('Invalid tenant id');
        $controller = new TenantController();
        $response = $controller->show($id);
        $tenant = $response['body']['data'] ?? null;
        if (!$tenant) throw new Exception('Tenant not found');
        include __DIR__ . '/../app/views/admin/tenant_show.php';
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (is_callable($renderError)) $renderError($msg, 404); else echo $msg;
    }
}, [$requireSuperAdmin]);

// GET: Edit tenant form (legacy path)
$router->add('GET', '/admin/tenants/edit.php', function () use ($renderError) {
    try {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception('Invalid tenant id');
        $controller = new TenantController();
        $response = $controller->show($id);
        $tenant = $response['body']['data'] ?? null;
        if (!$tenant) throw new Exception('Tenant not found');
        include __DIR__ . '/../app/views/admin/tenant_edit.php';
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (is_callable($renderError)) $renderError($msg, 404); else echo $msg;
    }
}, [$requireSuperAdmin]);

// POST: Create tenant
$router->add('POST', '/admin/tenants/create.php', function () {
    try {
        $controller = new TenantController();
        $response = $controller->store($_POST);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 201,
            'message' => $response['body']['message'] ?? ($response['status'] === 201 ? 'Tenant created successfully.' : 'Error creating tenant.'),
            'api_key' => $response['body']['api_key'] ?? null,
            'data' => $response['body']['data'] ?? null,
        ];
    } catch (Throwable $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ];
    }
    header('Location: /admin/tenants');
}, [$requireSuperAdmin, $csrfCheck]);

// POST: Update tenant
$router->add('POST', '/admin/tenants/update.php', function () {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $controller = new TenantController();
        $response = $controller->update($id, $_POST);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'Tenant updated.',
        ];
    } catch (Throwable $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ];
    }
    header('Location: /admin/tenants');
}, [$requireSuperAdmin, $csrfCheck]);

// POST: Deactivate tenant
$router->add('POST', '/admin/tenants/deactivate.php', function () {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $controller = new TenantController();
        $response = $controller->deactivate($id);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'Tenant deactivated.',
        ];
    } catch (Throwable $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ];
    }
    header('Location: /admin/tenants');
}, [$requireSuperAdmin, $csrfCheck]);

// POST: Activate tenant
$router->add('POST', '/admin/tenants/activate.php', function () {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $controller = new TenantController();
        $response = $controller->activate($id);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'Tenant activated.',
        ];
    } catch (Throwable $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ];
    }
    header('Location: /admin/tenants');
}, [$requireSuperAdmin, $csrfCheck]);

// POST: Rotate API key
$router->add('POST', '/admin/tenants/rotate_api_key.php', function () {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $controller = new TenantController();
        $response = $controller->rotateApiKey($id);
        $_SESSION['tenant_create_feedback'] = [
            'success' => $response['status'] === 200,
            'message' => $response['body']['message'] ?? 'API key rotated.',
            'api_key' => $response['body']['api_key'] ?? null,
        ];
    } catch (Throwable $e) {
        $_SESSION['tenant_create_feedback'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ];
    }
    header('Location: /admin/tenants');
}, [$requireSuperAdmin, $csrfCheck]);
