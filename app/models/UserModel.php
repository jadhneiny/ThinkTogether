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
    public function updateUser($id, $data) {
        // Dynamically build the query based on provided fields
        $fields = [];
        $params = ['id' => $id];

        if (!empty($data['name'])) {
            $fields[] = "Name = :name";
            $params['name'] = $data['name'];
        }
        if (!empty($data['username'])) {
            $fields[] = "Username = :username";
            $params['username'] = $data['username'];
        }
        if (!empty($data['email'])) {
            $fields[] = "Email = :email";
            $params['email'] = $data['email'];
        }
        if (!empty($data['password'])) {
            $fields[] = "Password = :password";
            $params['password'] = $data['password'];
        }
        if (!empty($data['role'])) {
            $fields[] = "Role = :role";
            $params['role'] = $data['role'];
        }

        if (empty($fields)) {
            return false; // No fields to update
        }

        $sql = "UPDATE User SET " . implode(", ", $fields) . " WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    // ✅ Delete user
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM User WHERE Id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
?>
