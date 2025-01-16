<?php
require_once '../app/models/UserModel.php';

class UserController {
    private $model;
    private $pdo;


    public function __construct($pdo) {
        $this->model = new UserModel($pdo);
    }

    // ✅ Register a new user
    public function registerUser() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Please fill in all required fields."]);
            return;
        }

        // ✅ Check if user exists
        $existingUser = $this->model->findUserByEmail($data['email']);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(["message" => "Email already in use."]);
            return;
        }

        // ✅ Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // ✅ Prepare user data
        $userData = [
            'name' => $data['name'],
            'username' => $data['username'] ?? '',
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => $data['role'] ?? 'user'
        ];

        // ✅ Insert user
        $id = $this->model->createUser($userData);
        echo json_encode(["message" => "User registered successfully!", "id" => $id]);
    }

    // ✅ Login user
    public function loginUser() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Email and password are required."]);
            return;
        }

        // ✅ Find user by email
        $user = $this->model->findUserByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['Password'])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid email or password."]);
            return;
        }

        // ✅ Login successful
        echo json_encode(["message" => "Login successful!", "user" => $user]);
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
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
        }
    }

        // ✅ Fetch the current logged-in user
        public function getCurrentUser() {
            session_start();
    
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(["message" => "User not logged in."]);
                return;
            }
    
            $userId = $_SESSION['user_id'];
    
            try {
                $stmt = $this->pdo->prepare("SELECT Id, Name, Username, Email, Role, ReputationPoints, CreatedAt FROM User WHERE Id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "User not found."]);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => $e->getMessage()]);
            }
        }
}
?>
