<?php
namespace App\Models;

use PDO;

class ActivityLog extends Model {
    protected string $table = 'activity_logs';

    public function getRecent(int $businessId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT l.*, u.name as user_name
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.business_id = :business_id
            ORDER BY l.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function log(int $businessId, int $userId, string $action, string $entityType, int $entityId, string $description): void {
        $this->create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'created_at' => now(),
        ]);
    }
}
