<?php

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Basic routing
if ($requestUri === '/' || $requestUri === '/login') {
    $controller = new \App\Controllers\AuthController();
    if ($method === 'GET') {
        $controller->showLogin();
    } elseif ($method === 'POST') {
        $controller->login();
    }
    exit;
}

if ($requestUri === '/logout') {
    $controller = new \App\Controllers\AuthController();
    $controller->logout();
    exit;
}

if ($requestUri === '/dashboard') {
    $controller = new \App\Controllers\DashboardController();
    $controller->index();
    exit;
}

if ($requestUri === '/agents') {
    $controller = new \App\Controllers\AgentController();
    $controller->index();
    exit;
}

if ($requestUri === '/agents/create') {
    $controller = new \App\Controllers\AgentController();
    $controller->create();
    exit;
}

if ($requestUri === '/agents/store' && $method === 'POST') {
    $controller = new \App\Controllers\AgentController();
    $controller->store();
    exit;
}

if ($requestUri === '/purchases') {
    $controller = new \App\Controllers\PurchaseController();
    $controller->index();
    exit;
}

if ($requestUri === '/purchases/export') {
    $controller = new \App\Controllers\PurchaseController();
    $controller->export();
    exit;
}

if ($requestUri === '/purchases/create') {
    $controller = new \App\Controllers\PurchaseController();
    $controller->create();
    exit;
}

if ($requestUri === '/purchases/store' && $method === 'POST') {
    $controller = new \App\Controllers\PurchaseController();
    $controller->store();
    exit;
}

if ($requestUri === '/purchases/delete' && $method === 'POST') {
    $controller = new \App\Controllers\PurchaseController();
    $controller->delete();
    exit;
}

if ($requestUri === '/crates') {
    $controller = new \App\Controllers\CrateReturnController();
    $controller->index();
    exit;
}

if ($requestUri === '/crates/export') {
    $controller = new \App\Controllers\CrateReturnController();
    $controller->export();
    exit;
}

if ($requestUri === '/crates/create') {
    $controller = new \App\Controllers\CrateReturnController();
    $controller->create();
    exit;
}

if ($requestUri === '/crates/store' && $method === 'POST') {
    $controller = new \App\Controllers\CrateReturnController();
    $controller->store();
    exit;
}

if ($requestUri === '/crates/delete' && $method === 'POST') {
    $controller = new \App\Controllers\CrateReturnController();
    $controller->delete();
    exit;
}

if ($requestUri === '/agents/export') {
    $controller = new \App\Controllers\AgentController();
    $controller->export();
    exit;
}

if ($requestUri === '/agents/show') {
    $controller = new \App\Controllers\AgentController();
    $controller->show();
    exit;
}

if ($requestUri === '/agents/update' && $method === 'POST') {
    $controller = new \App\Controllers\AgentController();
    $controller->update();
    exit;
}

if ($requestUri === '/agents/delete' && $method === 'POST') {
    $controller = new \App\Controllers\AgentController();
    $controller->delete();
    exit;
}

if ($requestUri === '/goals') {
    $controller = new \App\Controllers\GoalController();
    $controller->index();
    exit;
}

if ($requestUri === '/goals/create') {
    $controller = new \App\Controllers\GoalController();
    $controller->create();
    exit;
}

if ($requestUri === '/goals/store' && $method === 'POST') {
    $controller = new \App\Controllers\GoalController();
    $controller->store();
    exit;
}

if ($requestUri === '/goals/complete' && $method === 'POST') {
    $controller = new \App\Controllers\GoalController();
    $controller->complete();
    exit;
}

if ($requestUri === '/goals/delete' && $method === 'POST') {
    $controller = new \App\Controllers\GoalController();
    $controller->delete();
    exit;
}

if ($requestUri === '/ristourne') {
    $controller = new \App\Controllers\RistourneController();
    $controller->index();
    exit;
}

if ($requestUri === '/ristourne/rate/store' && $method === 'POST') {
    $controller = new \App\Controllers\RistourneController();
    $controller->storeRate();
    exit;
}

if ($requestUri === '/ristourne/quarter/update' && $method === 'POST') {
    $controller = new \App\Controllers\RistourneController();
    $controller->updateQuarter();
    exit;
}

if ($requestUri === '/reports') {
    $controller = new \App\Controllers\ReportController();
    $controller->index();
    exit;
}

if ($requestUri === '/payment-request') {
    $controller = new \App\Controllers\ReportController();
    $controller->paymentRequest();
    exit;
}

if ($requestUri === '/users') {
    $controller = new \App\Controllers\UserController();
    $controller->index();
    exit;
}

if ($requestUri === '/users/create') {
    $controller = new \App\Controllers\UserController();
    $controller->create();
    exit;
}

if ($requestUri === '/users/store' && $method === 'POST') {
    $controller = new \App\Controllers\UserController();
    $controller->store();
    exit;
}

if ($requestUri === '/users/toggle-status' && $method === 'POST') {
    $controller = new \App\Controllers\UserController();
    $controller->toggleStatus();
    exit;
}

if ($requestUri === '/settings') {
    $controller = new \App\Controllers\SettingsController();
    $controller->index();
    exit;
}

if ($requestUri === '/settings/update' && $method === 'POST') {
    $controller = new \App\Controllers\SettingsController();
    $controller->update();
    exit;
}

http_response_code(404);
echo "404 Not Found";
