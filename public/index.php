<?php
require_once '../config/db_connection.php';
require_once '../config/routes.php';

// ✅ Database Connection
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=IDS_my_database", "root", "");
    echo "Database connection successful!<br>";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// ✅ Load Routes
$routes = require '../config/routes.php';

// ✅ Get and normalize URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// ✅ Debug: Show requested URI and method
echo "Requested URI: $uri, Method: $method<br>";

// ✅ Normalize the URI (remove "/ThinkTogether/public")
$uri = str_replace('/ThinkTogether/public', '', $uri);
$uri = rtrim($uri, '/');  // Remove trailing slashes

echo "Normalized URI: $uri<br>";  // Debug normalized URI

// ✅ Route Matching
foreach ($routes as $route => $controllerAction) {
    // Split method and path from the route
    [$routeMethod, $routePath] = explode(' ', $route);

    // ✅ Ensure method matches
    if (strtoupper($method) !== strtoupper($routeMethod)) {
        continue;  // Method does not match, move to the next route
    }

    // ✅ Convert route path to regex
    $routePattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $routePath);
    $routePattern = str_replace('/', '\/', $routePattern);

    echo "Checking pattern: ^$routePattern$ against URI: $uri<br>";

    // ✅ Check if the URI matches the route pattern
    if (preg_match("/^$routePattern$/", $uri, $matches)) {
        list($controller, $action) = explode('@', $controllerAction);

        echo "Matched! Loading controller: $controller and action: $action<br>";

        // ✅ Load the controller
        $controllerPath = "../app/controllers/$controller.php";
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
        } else {
            echo "Controller file not found: $controllerPath<br>";
            http_response_code(500);
            exit;
        }

        // ✅ Initialize controller
        $controllerInstance = new $controller($pdo);

        // ✅ Call the controller action
        array_shift($matches);  // Remove full match
        call_user_func_array([$controllerInstance, $action], $matches);
        exit;
    }
}

// ✅ No route matched
http_response_code(404);
echo json_encode(["message" => "Route not found"]);
?>
