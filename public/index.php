<?php

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/db_connection.php';
require_once '../config/routes.php';

// Database Connection
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=IDS_my_database", "root", "");
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    http_response_code(500);
    exit;
}

// Load Routes
$routes = require '../config/routes.php';

// Get and normalize URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Normalize the URI (remove "/ThinkTogether/public")
$uri = str_replace('/ThinkTogether/public', '', $uri);
$uri = rtrim($uri, '/');  // Remove trailing slashes

// Route Matching
foreach ($routes as $route => $controllerAction) {
    // Split method and path from the route
    [$routeMethod, $routePath] = explode(' ', $route);

    // Ensure method matches
    if (strtoupper($method) !== strtoupper($routeMethod)) {
        continue;  // Method does not match, move to the next route
    }

    // Convert route path to regex
    $routePattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $routePath);
    $routePattern = str_replace('/', '\/', $routePattern);

    // Check if the URI matches the route pattern
    if (preg_match("/^$routePattern$/", $uri, $matches)) {
        list($controller, $action) = explode('@', $controllerAction);

        // Load the controller
        $controllerPath = "../app/controllers/$controller.php";
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(["error" => "Controller file not found: $controllerPath"]);
            exit;
        }

        // Initialize controller
        $controllerInstance = new $controller($pdo);

        // Call the controller action
        array_shift($matches);  // Remove full match
        header('Content-Type: application/json');  // Set header to JSON
        call_user_func_array([$controllerInstance, $action], $matches);
        exit;
    }
}

// No route matched
header('Content-Type: application/json');
http_response_code(404);
echo json_encode(["error" => "Route not found"]);
?>
