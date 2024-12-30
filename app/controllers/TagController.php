<?php
require_once '../app/models/TagModel.php';

class TagController {
    private $model;

    public function __construct($pdo) {
        $this->model = new TagModel($pdo);
    }

    public function getAllTags() {
        $tags = $this->model->getAllTags();
        echo json_encode($tags);
    }

    public function createTag() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createTag($data);
        echo json_encode(["id" => $id]);
    }
}
