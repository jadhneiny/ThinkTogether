<?php
class CategoryModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllCategories() {
        $stmt = $this->pdo->query("SELECT * FROM Category");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCategory($data) {
        $sql = "INSERT INTO Category (Name) VALUES (:name)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }
}
