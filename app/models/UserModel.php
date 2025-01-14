<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Fetch all users
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM User");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Fetch a user by ID
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE Id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Create a new user
    public function createUser($data) {
        $sql = "INSERT INTO User (Name, Username, Email, Password, Role) 
                VALUES (:name, :username, :email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role']
        ]);
    }

    // ✅ Find user by email
    public function findUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Update user
    public function updateUser($id, $data) {
        $sql = "UPDATE User SET Name = :name, Username = :username, Email = :email, Password = :password, Role = :role WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);

        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // ✅ Delete user
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM User WHERE Id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
?>
