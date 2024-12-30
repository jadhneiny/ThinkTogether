<?php
require_once '../app/models/VoteModel.php';

class VoteController {
    private $model;

    public function __construct($pdo) {
        $this->model = new VoteModel($pdo);
    }

    public function getAllVotes() {
        $votes = $this->model->getAllVotes();
        echo json_encode($votes);
    }

    public function getVoteById($id) {
        $vote = $this->model->getVoteById($id);
        if ($vote) {
            echo json_encode($vote);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Vote not found"]);
        }
    }

    public function createVote() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createVote($data);
        echo json_encode(["id" => $id]);
    }

    public function deleteVote($id) {
        $rowCount = $this->model->deleteVote($id);
        echo json_encode(["deleted" => $rowCount > 0]);
    }
}
