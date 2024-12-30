<?php
require_once '../app/models/PostTagModel.php';

class PostTagController {
    private $model;

    public function __construct($pdo) {
        $this->model = new PostTagModel($pdo);
    }

    public function createPostTag() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createPostTag($data);
        echo json_encode(["id" => $id]);
    }
}
