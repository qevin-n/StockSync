<?php
namespace App\Models;

use PDO;

class Supplier extends Model {
    protected string $table = 'suppliers';

    public function getAllWithStats(
        int $businessId,
        int $page = 1,
        int $perPage = 20,
        string $search = '',
        string $status = ''
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $where = "s.business_id = :business_id";
        $params = ['business_id' => $businessId];

        if ($search !== '') {
            $where .= " AND (
                s.company_name LIKE :search
                OR s.contact_person LIKE :search
                OR s.email LIKE :search
                OR s.phone LIKE :search
            )";
            $params['search'] = "%{$search}%";
        }

        if ($status !== '') {
            $where .= " AND s.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT
                    s.*,
                    (
                        SELECT COUNT(*)
                        FROM products p
                        WHERE p.supplier_id = s.id
                          AND p.business_id = s.business_id
                    ) AS product_count,
                    (
                        SELECT COUNT(*)
                        FROM purchases pur
                        WHERE pur.supplier_id = s.id
                          AND pur.business_id = s.business_id
                    ) AS purchase_count,
                    (
                        SELECT COALESCE(SUM(pur.final_amount), 0)
                        FROM purchases pur
                        WHERE pur.supplier_id = s.id
                          AND pur.business_id = s.business_id
                          AND pur.status = 'received'
                    ) AS total_purchases
                FROM suppliers s
                WHERE {$where}
                ORDER BY s.company_name
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*)
                     FROM suppliers s
                     WHERE {$where}";

        $countStmt = $this->db->prepare($countSql);

        foreach ($params as $key => $value) {
            $countStmt->bindValue(
                ':' . $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
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

    public function getPurchaseHistory(int $supplierId, int $businessId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, p.purchase_number AS reference_number, u.name AS created_by
            FROM purchases p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.supplier_id = :supplier_id
              AND p.business_id = :business_id
            ORDER BY p.purchase_date DESC
        ");

        $stmt->execute([
            'supplier_id' => $supplierId,
            'business_id' => $businessId
        ]);

        return $stmt->fetchAll();
    }
}
