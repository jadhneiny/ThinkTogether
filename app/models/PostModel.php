<?php
class PostModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllPosts() {
        $stmt = $this->pdo->query("SELECT * FROM Post");
        return $stmt->fetchAll();
    }

    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createPost($data) {
        $sql = "INSERT INTO Post (UserId, Title, Description, CategoryId, Link, CodeSnippet) VALUES (:userId, :title, :description, :categoryId, :link, :codeSnippet)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function updatePost($id, $data) {
        $sql = "UPDATE Post SET Title = :title, Description = :description, CategoryId = :categoryId, Link = :link, CodeSnippet = :codeSnippet WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    public function deletePost($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Post WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
