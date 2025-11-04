<?php
// Front controller for PHP MVC app
// All requests routed through this file

$uri = $_SERVER['REQUEST_URI'];

// Static assets bypass (css/js/images)
if (preg_match('#^/(assets|favicon\.ico|robots\.txt)#', $uri)) {
    return false; // Let Apache serve static files
}

// Route all other requests through web.php
require_once __DIR__ . '/../routes/web.php';
