<?php
class NotificationModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllNotifications() {
        $stmt = $this->pdo->query("SELECT * FROM Notification");
        return $stmt->fetchAll();
    }

    public function getNotificationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Notification WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createNotification($data) {
        $sql = "INSERT INTO Notification (UserId, Message, NotificationType, IsRead) VALUES (:userId, :message, :notificationType, :isRead)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function deleteNotification($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Notification WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
