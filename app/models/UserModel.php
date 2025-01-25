<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Fetch all users
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT Id, Name, Username, Email, Role, ReputationPoints, CreatedAt, UpdatedAt FROM User");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Fetch a user by ID
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT Id, Name, Username, Email, Role, ReputationPoints, CreatedAt, UpdatedAt FROM User WHERE Id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Create a new user
    public function createUser($data) {
        $sql = "INSERT INTO User (Name, Username, Email, Password, Role) 
                VALUES (:name, :username, :email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);

        if ($stmt->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role']
        ])) {
            return $this->pdo->lastInsertId(); // Return the ID of the created user
        }

        return false;
    }

    // ✅ Find user by email
    public function findUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM User WHERE Email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;  // Handle the error gracefully
        }
    }
    
    // ✅ Update user
    public function updateUser($id) {
        // Log the raw input for debugging
        $rawInput = file_get_contents("php://input");
        error_log("Raw input: " . $rawInput);
    
        // Decode the JSON input
        $data = json_decode($rawInput, true);
    
        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid JSON input."]);
            error_log("JSON parsing error: " . json_last_error_msg());
            exit; // Stop further execution
        }
    
        // Validate input
        if (empty($data['name']) && empty($data['email']) && empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "At least one field is required to update."]);
            exit; // Stop further execution
        }
    
        // Check if the user exists
        $user = $this->getUserById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
            exit; // Stop further execution
        }
    
        // Prepare the update query
        $fields = [];
        $params = ['id' => $id];
    
        if (!empty($data['name'])) {
            $fields[] = "Name = :name";
            $params['name'] = $data['name'];
        }
        if (!empty($data['email'])) {
            $fields[] = "Email = :email";
            $params['email'] = $data['email'];
        }
        if (!empty($data['password'])) {
            $fields[] = "Password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
    
        $sql = "UPDATE User SET " . implode(", ", $fields) . " WHERE Id = :id";
    
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
    
            // Check if rows were actually updated
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["message" => "User updated successfully."]);
                exit; // Stop further execution after success
            } else {
                http_response_code(200); // Still OK, but no changes
                echo json_encode(["message" => "No changes were made to the user."]);
                exit; // Stop further execution
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["message" => "Failed to update user."]);
            exit; // Stop further execution
        }
    }    
    // ✅ Delete user
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM User WHERE Id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }

        // Get all posts by a user
        public function getPostsByUserId($userId) {
            $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE UserId = ?");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        // Get all comments by a user
        public function getCommentsByUserId($userId) {
            $stmt = $this->pdo->prepare("SELECT * FROM Comment WHERE UserId = ?");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
}
?>
