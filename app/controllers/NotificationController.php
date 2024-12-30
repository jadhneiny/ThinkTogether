<?php
require_once '../app/models/NotificationModel.php';

class NotificationController {
    private $model;

    public function __construct($pdo) {
        $this->model = new NotificationModel($pdo);
    }

    public function getAllNotifications() {
        $notifications = $this->model->getAllNotifications();
        echo json_encode($notifications);
    }

    public function getNotificationById($id) {
        $notification = $this->model->getNotificationById($id);
        if ($notification) {
            echo json_encode($notification);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Notification not found"]);
        }
    }

    public function createNotification() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->model->createNotification($data);
        echo json_encode(["id" => $id]);
    }

    public function deleteNotification($id) {
        $rowCount = $this->model->deleteNotification($id);
        echo json_encode(["deleted" => $rowCount > 0]);
    }
}
