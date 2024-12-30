<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM User");
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($data) {
        $sql = "INSERT INTO User (Name, Username, Email, Password, Role) VALUES (:name, :username, :email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function updateUser($id, $data) {
        $sql = "UPDATE User SET Name = :name, Username = :username, Email = :email, Password = :password, Role = :role WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM User WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
