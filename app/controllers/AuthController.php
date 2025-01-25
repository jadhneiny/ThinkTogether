<?php
require_once '../vendor/autoload.php';  // ✅ Composer autoload for JWT
require_once '../app/models/UserModel.php';
require_once '../config/db_connection.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $model;
    private $secretKey;

    public function __construct($pdo) {
        $this->model = new UserModel($pdo);
        $this->secretKey = getenv('JWT_SECRET') ?: 'hellothisismysecretkeyforthinktogether';  // 🔒 Use ENV for security
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
    
        if (!$data) {
            http_response_code(400);
            echo json_encode(["message" => "No input data provided."]);
            return;
        }
    
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
    
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["message" => "Email and password are required."]);
            return;
        }
    
        $user = $this->model->findUserByEmail($email);
    
        if (!$user) {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
            return;
        }
    
        if (!password_verify($password, $user['Password'])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid email or password."]);
            return;
        }
    
        try {
            $payload = [
                'iss' => 'http://localhost',
                'aud' => 'http://localhost',
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24),
                'data' => [
                    'id' => $user['Id'],
                    'name' => $user['Name'],
                    'email' => $user['Email'],
                    'role' => $user['Role']
                ]
            ];
    
            $jwt = JWT::encode($payload, $this->secretKey, 'HS256');
    
            http_response_code(200);
            echo json_encode([
                "message" => "Login successful.",
                "token" => $jwt
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Token generation failed: " . $e->getMessage());
            echo json_encode(["message" => "Token generation failed."]);
        }
    }
    

    // ✅ Get Current User from JWT
    public function getCurrentUser() {
        $headers = apache_request_headers();
        error_log("Headers: " . print_r($headers, true)); // Debug log
    
        $authHeader = $headers['Authorization'] ?? '';
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(["message" => "Authorization header not found."]);
            error_log("Authorization header missing or invalid.");
            return;
        }
    
        $jwt = $matches[1];
        error_log("JWT Token: " . $jwt); // Debug log
    
        try {
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            error_log("Decoded JWT: " . print_r($decoded, true)); // Debug log
    
            $userId = $decoded->data->id ?? null; // Extract user ID
            if (!$userId) {
                http_response_code(404);
                echo json_encode(["message" => "User ID not found in token."]);
                error_log("User ID missing from token.");
                return;
            }
    
            $user = $this->model->getUserById($userId);
            error_log("Database query result: " . print_r($user, true));
    
            if (!$user) {
                http_response_code(404);
                echo json_encode(["message" => "User was not found."]);
                error_log("User not found for ID: " . $userId); // Debug log
                return;
            }
    
            unset($user['Password']); // Remove sensitive data
            http_response_code(200);
            echo json_encode($user);
            error_log("Returning user data: " . json_encode($user));
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Token decoding failed: " . $e->getMessage());
            echo json_encode(["message" => "Token decoding failed."]);
        }
    }        
    
    
}
