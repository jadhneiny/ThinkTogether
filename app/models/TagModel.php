<?php
class TagModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllTags() {
        $stmt = $this->pdo->query("SELECT * FROM Tag");
        return $stmt->fetchAll();
    }

    public function createTag($data) {
        $sql = "INSERT INTO Tag (TagName) VALUES (:tagName)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }
}
