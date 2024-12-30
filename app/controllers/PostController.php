<?php
require_once '../app/models/PostModel.php';

class PostController {
    private $model;

    public function __construct($pdo) {
        $this->model = new PostModel($pdo);
    }

    public function getAllPosts() {
        $posts = $this->model->getAllPosts();
        echo json_encode($posts);
    }

    public function getPostById($id) {
        $post = $this->model->getPostById($id);
        if ($post) {
            echo json_encode($post);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Post not found"]);
        }
    }

    public function createPost() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createPost($data);
        echo json_encode(["id" => $id]);
    }

    public function updatePost($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $rowCount = $this->model->updatePost($id, $data);
        echo json_encode(["updated" => $rowCount > 0]);
    }

    public function deletePost($id) {
        $rowCount = $this->model->deletePost($id);
        echo json_encode(["deleted" => $rowCount > 0]);
    }
}
