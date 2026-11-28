<?php
namespace App\Models;

use PDO;

class StockMovement extends Model {
    protected string $table = 'stock_movements';

    public function getAllWithRelations(int $businessId, int $page = 1, int $perPage = 20, string $search = '', string $type = ''): array {
        $where = "sm.business_id = :business_id";
        $params = ['business_id' => $businessId];

        if ($search !== '') {
            $where .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($type !== '') {
            $where .= " AND sm.type = :type";
            $params['type'] = $type;
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT sm.*, p.name AS product_name, p.sku, NULL AS image, u.name AS user_name
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                LEFT JOIN users u ON sm.created_by = u.id
                WHERE {$where}
                ORDER BY sm.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $paramType);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*)
                     FROM stock_movements sm
                     JOIN products p ON sm.product_id = p.id
                     WHERE {$where}";

        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $countStmt->bindValue(':' . $key, $value, $paramType);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();
        $totalPages = (int) ceil($total / max(1, $perPage));

        return [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
        ];
    }

    public function getByProduct(int $productId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT sm.*, p.name AS product_name, p.sku, NULL AS image, u.name AS user_name
            FROM stock_movements sm
            JOIN products p ON sm.product_id = p.id
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE sm.product_id = :product_id
            ORDER BY sm.created_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function log(
        int $businessId,
        int $productId,
        int $userId,
        string $type,
        int $quantity,
        int $previousQty,
        int $newQty,
        string $referenceType = '',
        int $referenceId = 0,
        string $notes = ''
    ): int {
        $movementType = match ($type) {
            'sale', 'out' => 'out',
            'purchase', 'in' => 'in',
            'adjustment' => 'adjustment',
            default => throw new \InvalidArgumentException('Invalid stock movement type: ' . $type),
        };

        $storedQuantity = $movementType === 'adjustment'
            ? $quantity
            : abs($quantity);

        $movementNotes = trim($notes);
        $movementNotes .= ($movementNotes !== '' ? ' ' : '')
            . 'Stock changed from ' . $previousQty . ' to ' . $newQty . '.';

        return $this->create([
            'business_id' => $businessId,
            'product_id' => $productId,
            'type' => $movementType,
            'quantity' => $storedQuantity,
            'reference_type' => $referenceType !== '' ? $referenceType : null,
            'reference_id' => $referenceId > 0 ? $referenceId : null,
            'notes' => $movementNotes,
            'created_by' => $userId > 0 ? $userId : null,
        ]);
    }
}
