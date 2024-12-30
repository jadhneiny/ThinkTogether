<?php
class PostTagModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createPostTag($data) {
        $sql = "INSERT INTO PostTag (PostId, TagId) VALUES (:postId, :tagId)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }
}
