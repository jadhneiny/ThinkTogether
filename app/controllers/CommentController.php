<?php
require_once '../app/models/CommentModel.php';

class CommentController {
    private $model;

    public function __construct($pdo) {
        $this->model = new CommentModel($pdo);
    }

    public function getAllComments() {
        $comments = $this->model->getAllComments();
        echo json_encode($comments);
    }

    public function getCommentById($id) {
        $comment = $this->model->getCommentById($id);
        if ($comment) {
            echo json_encode($comment);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Comment not found"]);
        }
    }

    public function createComment() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createComment($data);
        echo json_encode(["id" => $id]);
    }

    public function updateComment($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $rowCount = $this->model->updateComment($id, $data);
        echo json_encode(["updated" => $rowCount > 0]);
    }

    public function deleteComment($id) {
        $rowCount = $this->model->deleteComment($id);
        echo json_encode(["deleted" => $rowCount > 0]);
    }
}
