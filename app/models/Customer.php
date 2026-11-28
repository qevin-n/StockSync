<?php
namespace App\Models;

use PDO;

class Customer extends Model {
    protected string $table = 'customers';

    public function getAllWithStats(
        int $businessId,
        int $page = 1,
        int $perPage = 20,
        string $search = ''
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $where = "c.business_id = :business_id";
        $params = ['business_id' => $businessId];

        if ($search !== '') {
            $where .= " AND (
                c.name LIKE :search_name
                OR c.email LIKE :search_email
                OR c.phone LIKE :search_phone
            )";
            $searchValue = "%{$search}%";
            $params['search_name'] = $searchValue;
            $params['search_email'] = $searchValue;
            $params['search_phone'] = $searchValue;
        }

        $sql = "SELECT c.*,
                       COUNT(DISTINCT s.id) AS order_count,
                       COALESCE(SUM(s.final_amount), 0) AS total_spent,
                       MAX(s.sale_date) AS last_purchase
                FROM customers c
                LEFT JOIN sales s
                    ON c.id = s.customer_id
                    AND c.business_id = s.business_id
                    AND s.status = 'completed'
                WHERE {$where}
                GROUP BY c.id
                ORDER BY c.name
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
                     FROM customers c
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

    public function getOrderHistory(int $customerId, int $businessId): array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name as created_by
            FROM sales s
            LEFT JOIN users u ON s.created_by = u.id
            WHERE s.customer_id = :customer_id AND s.business_id = :business_id
            ORDER BY s.sale_date DESC
        ");
        $stmt->execute(['customer_id' => $customerId, 'business_id' => $businessId]);
        return $stmt->fetchAll();
    }
}
