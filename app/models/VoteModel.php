<?php
class VoteModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllVotes() {
        $stmt = $this->pdo->query("SELECT * FROM Vote");
        return $stmt->fetchAll();
    }

    public function getVoteById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Vote WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createVote($data) {
        $sql = "INSERT INTO Vote (PostId, UserId, VoteType) VALUES (:postId, :userId, :voteType)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function deleteVote($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Vote WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
