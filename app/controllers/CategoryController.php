<?php
require_once '../app/models/CategoryModel.php';

class CategoryController {
    private $model;

    public function __construct($pdo) {
        $this->model = new CategoryModel($pdo);
    }

    public function getAllCategories() {
        $categories = $this->model->getAllCategories();
        echo json_encode($categories);
    }

    public function createCategory() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createCategory($data);
        echo json_encode(["id" => $id]);
    }
}
