<?php
require_once '../config/db_connection.php';
require_once '../config/routes.php';

$pdo = new PDO("mysql:host=127.0.0.1;dbname=IDS_my_database", "root", "");

$routes = require '../config/routes.php';
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Find matching route
foreach ($routes as $route => $controllerAction) {
    $route = preg_replace('/{[a-zA-Z0-9_]+}/', '([a-zA-Z0-9_]+)', $route);
    $route = str_replace('/', '\/', $route);
    if (preg_match("/^$route$/", "$method $uri", $matches)) {
        list($controller, $action) = explode('@', $controllerAction);
        require_once "../app/controllers/$controller.php";
        $controllerInstance = new $controller($pdo);
        array_shift($matches); // Remove full match
        call_user_func_array([$controllerInstance, $action], $matches);
        exit;
    }
}

http_response_code(404);
echo json_encode(["message" => "Route not found"]);
