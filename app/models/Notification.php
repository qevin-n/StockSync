<?php
namespace App\Models;

use PDO;

class Notification extends Model {
    protected string $table = 'notifications';

    public function getUnread(int $businessId, int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE business_id = :business_id AND (user_id = :user_id OR user_id = 0)
              AND is_read = 0
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $businessId, int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE business_id = :business_id AND (user_id = :user_id OR user_id = 0)
              AND is_read = 0
        ");
        $stmt->execute(['business_id' => $businessId, 'user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAsRead(int $id): bool {
        return $this->update($id, ['is_read' => 1]);
    }

    public function markAllAsRead(int $businessId, int $userId): void {
        $stmt = $this->db->prepare("
            UPDATE notifications SET is_read = 1
            WHERE business_id = :business_id AND (user_id = :user_id OR user_id = 0) AND is_read = 0
        ");
        $stmt->execute(['business_id' => $businessId, 'user_id' => $userId]);
    }

    public function createNotification(int $businessId, int $userId, string $type, string $title, string $message): int {
        return $this->create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
        ]);
    }
}
