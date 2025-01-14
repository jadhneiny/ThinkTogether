<?php
require_once '../app/models/UserModel.php';

class AuthController {
    private $model;

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

    // ✅ User Login
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

        // ✅ Successful Login
        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.",
            "user" => [
                "id" => $user['Id'],
                "name" => $user['Name'],
                "username" => $user['Username'],
                "email" => $user['Email'],
                "role" => $user['Role']
            ]
        ]);
    }
}
?>
