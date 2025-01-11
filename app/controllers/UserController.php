<?php
require_once '../app/models/UserModel.php';

class UserController {
    private $model;

    public function __construct($pdo) {
        $this->model = new UserModel($pdo);
    }

    public function getAllUsers() {
        $users = $this->model->getAllUsers();
        echo json_encode($users);
    }

    public function getUserById($id) {
        $user = $this->model->getUserById($id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
        }
    }

    public function createUser() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createUser($data);
        echo json_encode(["id" => $id]);
    }

    public function updateUser($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $rowCount = $this->model->updateUser($id, $data);
        echo json_encode(["updated" => $rowCount > 0]);
    }

    public function deleteUser($id) {
        $rowCount = $this->model->deleteUser($id);
        echo json_encode(["deleted" => $rowCount > 0]);
    }
    
    public function test() {
        echo "Test route working!";
    }
    
}
