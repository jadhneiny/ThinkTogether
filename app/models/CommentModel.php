<?php
class CommentModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllComments() {
        $stmt = $this->pdo->query("SELECT * FROM Comment");
        return $stmt->fetchAll();
    }

    public function getCommentById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Comment WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createComment($data) {
        $sql = "INSERT INTO Comment (PostId, UserId, Content, MentionedUserId) VALUES (:postId, :userId, :content, :mentionedUserId)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function updateComment($id, $data) {
        $sql = "UPDATE Comment SET Content = :content, MentionedUserId = :mentionedUserId WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    public function deleteComment($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Comment WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
