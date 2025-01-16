<?php
require_once '../vendor/autoload.php';  // ✅ Include Composer autoload for JWT
require_once '../app/models/UserModel.php';
require_once '../config/db_connection.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $model;
    private $secretKey = 'hellothisismysecretkeyforthinktogether';  // 🔒 Replace with a strong key

    public function __construct($pdo) {
        $this->model = new UserModel($pdo);
    }

    // ✅ User Registration
    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);

        $name = trim($data['name'] ?? '');
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        // 🚨 Input Validation
        if (empty($name) || empty($username) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["message" => "All fields are required."]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid email format."]);
            return;
        }

        // 🔍 Check if user already exists
        if ($this->model->findUserByEmail($email)) {
            http_response_code(409);
            echo json_encode(["message" => "Email is already registered."]);
            return;
        }

        // 🔒 Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 📦 Prepare user data
        $userData = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'user'
        ];

        // 💾 Register user
        if ($this->model->createUser($userData)) {
            http_response_code(201);
            echo json_encode(["message" => "User registered successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to register user."]);
        }
    }

    // ✅ User Login with JWT Token
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        // 🚨 Input Validation
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["message" => "Email and password are required."]);
            return;
        }

        // 🔍 Check if user exists
        $user = $this->model->findUserByEmail($email);

        if (!$user || !password_verify($password, $user['Password'])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid email or password."]);
            return;
        }

        // ✅ Generate JWT Token
        $payload = [
            'iss' => 'http://localhost',       // Issuer
            'aud' => 'http://localhost',       // Audience
            'iat' => time(),                   // Issued at
            'exp' => time() + (60 * 60 * 24), // Expiration (1 day)
            'data' => [
                'id' => $user['Id'],
                'name' => $user['Name'],
                'email' => $user['Email'],
                'role' => $user['Role']
            ]
        ];

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

        // ✅ Send JWT Token
        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.",
            "token" => $jwt
        ]);
    }

    // ✅ Get Current User from JWT
    public function getCurrentUser() {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(["message" => "Authorization header not found."]);
            return;
        }

        $jwt = $matches[1];

        try {
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            $userId = $decoded->data->id;

            $user = $this->model->getUserById($userId);

            if ($user) {
                unset($user['Password']);  // 🔒 Remove password
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "User not found."]);
            }

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid or expired token."]);
        }
    }

    // 🚪 User Logout (Client-Side: Just delete the token)
    public function logout() {
        http_response_code(200);
        echo json_encode(["message" => "Logout successful. Please delete the token on the client side."]);
    }
}
?>
