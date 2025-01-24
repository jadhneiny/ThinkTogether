<?php
require_once '../app/models/UserModel.php';

class UserController {
    private $model;

    public function __construct($pdo) {
        $this->model = new UserModel($pdo);
    }

    // ✅ Get all users
    public function getAllUsers() {
        $users = $this->model->getAllUsers();
        echo json_encode($users);
    }

    // ✅ Get user by ID
    public function getUserById($id) {
        $user = $this->model->getUserById($id);
        if ($user) {
            unset($user['Password']); // 🔒 Remove password before sending
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
        }
    }

    // ✅ Create a new user
    public function createUser() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate input
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Name, email, and password are required."]);
            return;
        }

        // Check if the user already exists
        if ($this->model->findUserByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(["message" => "Email already in use."]);
            return;
        }

        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Prepare user data
        $userData = [
            'name' => $data['name'],
            'username' => $data['username'] ?? '',
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => $data['role'] ?? 'user'
        ];

        // Insert the user
        $id = $this->model->createUser($userData);
        if ($id) {
            http_response_code(201);
            echo json_encode(["message" => "User created successfully.", "id" => $id]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create user."]);
        }
    }

    // ✅ Update an existing user
    public function updateUser($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate input
        if (empty($data['name']) && empty($data['email']) && empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "At least one field is required to update."]);
            return;
        }

        // Check if the user exists
        $user = $this->model->getUserById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
            return;
        }

        // Hash the password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Update the user
        $updated = $this->model->updateUser($id, $data);
        if ($updated) {
            echo json_encode(["message" => "User updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update user."]);
        }
    }

    // ✅ Delete a user
    public function deleteUser($id) {
        // Check if the user exists
        $user = $this->model->getUserById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
            return;
        }

        // Delete the user
        $deleted = $this->model->deleteUser($id);
        if ($deleted) {
            echo json_encode(["message" => "User deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete user."]);
        }
    }
}
