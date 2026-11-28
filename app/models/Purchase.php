<?php
namespace App\Models;

use PDO;

class Purchase extends Model {
    protected string $table = 'purchases';

    public function getAllWithRelations(
        int $businessId,
        int $page = 1,
        int $perPage = 20,
        string $search = '',
        string $status = '',
        string $startDate = '',
        string $endDate = ''
    ): array {
        $where = "p.business_id = :business_id";
        $params = ['business_id' => $businessId];

        if ($search !== '') {
            $where .= " AND (p.purchase_number LIKE :search OR s.company_name LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== '') {
            $where .= " AND p.status = :status";
            $params['status'] = $status;
        }

        if ($startDate !== '') {
            $where .= " AND p.purchase_date >= :start_date";
            $params['start_date'] = $startDate;
        }

        if ($endDate !== '') {
            $where .= " AND p.purchase_date <= :end_date";
            $params['end_date'] = $endDate;
        }

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, p.purchase_number AS reference_number,
                       s.company_name AS supplier_name,
                       u.name AS created_by
                FROM purchases p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE {$where}
                ORDER BY p.purchase_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*)
                     FROM purchases p
                     LEFT JOIN suppliers s ON p.supplier_id = s.id
                     WHERE {$where}";

        $countStmt = $this->db->prepare($countSql);

        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        return [
            'data' => $items,
            'items' => $items,
            'current_page' => $page,
            'page' => $page,
            'per_page' => $perPage,
            'perPage' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
        ];
    }

    public function findByIdWithRelations(int $id, int $businessId): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, p.purchase_number AS reference_number, s.company_name as supplier_name, s.contact_person AS contact_name, s.email as supplier_email, s.phone as supplier_phone, s.address as supplier_address, u.name as created_by
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = :id AND p.business_id = :business_id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id, 'business_id' => $businessId]);
        return $stmt->fetch() ?: null;
    }

    public function getItems(int $purchaseId): array {
        $stmt = $this->db->prepare("
            SELECT pi.*, pi.unit_price AS unit_cost, pi.total_price AS subtotal, pi.quantity AS received_quantity, p.name as product_name, p.sku, NULL AS image
            FROM purchase_items pi
            JOIN products p ON pi.product_id = p.id
            WHERE pi.purchase_id = :purchase_id
        ");
        $stmt->execute(['purchase_id' => $purchaseId]);
        return $stmt->fetchAll();
    }

    public function createPurchase(array $purchaseData, array $items): int {
        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $purchaseId = $this->create($purchaseData);

            $stmt = $this->db->prepare("
                INSERT INTO purchase_items
                    (purchase_id, product_id, quantity, unit_price, total_price)
                VALUES
                    (:purchase_id, :product_id, :quantity, :unit_price, :total_price)
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    'purchase_id' => $purchaseId,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'total_price' => (float) $item['total_price'],
                ]);
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }

            return $purchaseId;
        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    public function updateStatus(int $id, int $businessId, string $status): bool {
        return $this->updateByBusiness($id, $businessId, ['status' => $status, 'updated_at' => now()]);
    }

    public function getTotalPurchases(int $businessId, string $startDate = '', string $endDate = ''): float {
        $sql = "SELECT COALESCE(SUM(final_amount), 0) FROM purchases WHERE business_id = :business_id AND status = 'received'";
        $params = ['business_id' => $businessId];
        if ($startDate && $endDate) {
            $sql .= " AND purchase_date BETWEEN :start AND :end";
            $params['start'] = $startDate;
            $params['end'] = $endDate;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function getPendingCount(int $businessId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM purchases WHERE business_id = :business_id AND status = 'pending'");
        $stmt->execute(['business_id' => $businessId]);
        return (int) $stmt->fetchColumn();
    }
}
