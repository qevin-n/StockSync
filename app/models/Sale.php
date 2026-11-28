<?php
namespace App\Models;

use PDO;

class Sale extends Model {
    protected string $table = 'sales';

    public function getAllWithRelations(
        int $businessId,
        int $page = 1,
        int $perPage = 20,
        string $search = '',
        string $status = '',
        string $startDate = '',
        string $endDate = ''
    ): array {
        $where = "s.business_id = :business_id";
        $params = ['business_id' => $businessId];

        if ($search !== '') {
            $where .= " AND (s.sale_number LIKE :search OR c.name LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== '') {
            $where .= " AND s.status = :status";
            $params['status'] = $status;
        }

        if ($startDate !== '') {
            $where .= " AND s.sale_date >= :start_date";
            $params['start_date'] = $startDate;
        }

        if ($endDate !== '') {
            $where .= " AND s.sale_date <= :end_date";
            $params['end_date'] = $endDate;
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT s.*, s.sale_number AS invoice_number,
                       c.name AS customer_name, u.name AS created_by
                FROM sales s
                LEFT JOIN customers c ON s.customer_id = c.id
                LEFT JOIN users u ON s.created_by = u.id
                WHERE {$where}
                ORDER BY s.sale_date DESC
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
                     FROM sales s
                     LEFT JOIN customers c ON s.customer_id = c.id
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
            SELECT s.*, s.sale_number AS invoice_number, c.name as customer_name, c.email as customer_email, c.phone as customer_phone, c.address as customer_address, u.name as created_by
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            LEFT JOIN users u ON s.created_by = u.id
            WHERE s.id = :id AND s.business_id = :business_id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id, 'business_id' => $businessId]);
        return $stmt->fetch() ?: null;
    }

    public function getItems(int $saleId): array {
        $stmt = $this->db->prepare("
            SELECT si.*, si.total_price AS subtotal, p.name as product_name, p.sku, NULL AS image
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = :sale_id
        ");
        $stmt->execute(['sale_id' => $saleId]);
        return $stmt->fetchAll();
    }

    public function createSale(array $saleData, array $items): int {
        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $saleId = $this->create($saleData);

            $stmt = $this->db->prepare("
                INSERT INTO sale_items
                    (sale_id, product_id, quantity, unit_price, total_price)
                VALUES
                    (:sale_id, :product_id, :quantity, :unit_price, :total_price)
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    'sale_id' => $saleId,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'total_price' => (float) $item['total_price'],
                ]);
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }

            return $saleId;
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

    public function getTotalSales(int $businessId, string $startDate = '', string $endDate = ''): float {
        $sql = "SELECT COALESCE(SUM(final_amount), 0) FROM sales WHERE business_id = :business_id AND status = 'completed'";
        $params = ['business_id' => $businessId];
        if ($startDate && $endDate) {
            $sql .= " AND sale_date BETWEEN :start AND :end";
            $params['start'] = $startDate;
            $params['end'] = $endDate;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function getSalesByDate(int $businessId, string $startDate, string $endDate): array {
        $stmt = $this->db->prepare("
            SELECT DATE(sale_date) as date, COUNT(*) as orders, SUM(final_amount) as total
            FROM sales
            WHERE business_id = :business_id AND status = 'completed' AND sale_date BETWEEN :start AND :end
            GROUP BY DATE(sale_date)
            ORDER BY date
        ");
        $stmt->execute(['business_id' => $businessId, 'start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll();
    }

    public function getTodaySales(int $businessId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(final_amount), 0) FROM sales
            WHERE business_id = :business_id AND status = 'completed' AND DATE(sale_date) = CURDATE()
        ");
        $stmt->execute(['business_id' => $businessId]);
        return (float) $stmt->fetchColumn();
    }

    public function getMonthlySales(int $businessId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(final_amount), 0) FROM sales
            WHERE business_id = :business_id AND status = 'completed' AND MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())
        ");
        $stmt->execute(['business_id' => $businessId]);
        return (float) $stmt->fetchColumn();
    }
}
