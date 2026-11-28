<?php

namespace App\Models;

use PDO;

class Product extends Model
{
    protected string $table = 'products';

    /**
     * Keep compatibility with existing views and controllers.
     */
    private function productSelect(): string
    {
        return "
            p.*,
            p.quantity AS stock_quantity,
            p.min_stock_level AS minimum_stock,
            NULL AS barcode,
            NULL AS image
        ";
    }

    public function findByIdAndBusiness(int $id, int $businessId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT " . $this->productSelect() . "
            FROM products p
            WHERE p.id = :id
              AND p.business_id = :business_id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
            'business_id' => $businessId
        ]);

        return $stmt->fetch() ?: null;
    }

    public function findByIdWithRelations(int $id, int $businessId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                " . $this->productSelect() . ",
                c.name AS category_name,
                s.company_name AS supplier_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.id = :id
              AND p.business_id = :business_id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
            'business_id' => $businessId
        ]);

        return $stmt->fetch() ?: null;
    }

    public function findBySkuAndBusiness(
        string $sku,
        int $businessId,
        ?int $excludeId = null
    ): ?array {
        $sql = "
            SELECT
                " . $this->productSelect() . "
            FROM products p
            WHERE p.sku = :sku
              AND p.business_id = :business_id
        ";

        $params = [
            'sku' => $sku,
            'business_id' => $businessId
        ];

        if ($excludeId !== null) {
            $sql .= " AND p.id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql . " LIMIT 1");
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }

    public function getAllWithRelations(
        int $businessId,
        int $page = 1,
        int $perPage = 20,
        string $search = '',
        string $category = '',
        string $status = ''
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $where = "p.business_id = :business_id";

        $params = [
            'business_id' => $businessId
        ];

        if ($search !== '') {
            $where .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($category !== '') {
            $where .= " AND p.category_id = :category_id";
            $params['category_id'] = (int) $category;
        }

        if ($status !== '') {
            $where .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                " . $this->productSelect() . ",
                c.name AS category_name,
                s.company_name AS supplier_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE {$where}
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        $items = $stmt->fetchAll();

        $countStmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM products p
            WHERE {$where}
        ");

        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $countStmt->bindValue(':' . $key, $value, $type);
        }

        $countStmt->execute();

        $total = (int) $countStmt->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages
        ];
    }

    public function getLowStock(int $businessId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                " . $this->productSelect() . ",
                c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.business_id = :business_id
              AND p.quantity <= p.min_stock_level
              AND p.status = 'active'
            ORDER BY p.quantity ASC
        ");

        $stmt->execute(['business_id' => $businessId]);

        return $stmt->fetchAll();
    }

    public function getOutOfStock(int $businessId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                " . $this->productSelect() . ",
                c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.business_id = :business_id
              AND p.quantity = 0
              AND p.status = 'active'
            ORDER BY p.name
        ");

        $stmt->execute(['business_id' => $businessId]);

        return $stmt->fetchAll();
    }

    public function getTopSelling(int $businessId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.name,
                p.sku,
                p.quantity AS stock_quantity,
                p.min_stock_level AS minimum_stock,
                SUM(si.quantity) AS total_sold,
                SUM(si.total_price) AS total_revenue
            FROM products p
            JOIN sale_items si ON p.id = si.product_id
            JOIN sales s ON si.sale_id = s.id
            WHERE p.business_id = :business_id
              AND s.status = 'completed'
            GROUP BY p.id, p.name, p.sku, p.quantity, p.min_stock_level
            ORDER BY total_sold DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function changeStockForBusiness(int $id, int $businessId, int $delta): bool
    {
        if ($delta === 0) {
            return true;
        }

        $stmt = $this->db->prepare("
            UPDATE products
            SET quantity = quantity + :delta,
                updated_at = NOW()
            WHERE id = :id
              AND business_id = :business_id
              AND quantity + :check_delta >= 0
        ");

        $stmt->execute([
            'delta' => $delta,
            'id' => $id,
            'business_id' => $businessId,
            'check_delta' => $delta,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function updateStock(int $id, int $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET quantity = quantity + :quantity,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'quantity' => $quantity,
            'id' => $id
        ]);
    }

    public function setStock(int $id, int $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET quantity = :quantity,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'quantity' => $quantity,
            'id' => $id
        ]);
    }

    public function getTotalValue(int $businessId): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(purchase_price * quantity), 0)
            FROM products
            WHERE business_id = :business_id
        ");

        $stmt->execute(['business_id' => $businessId]);

        return (float) $stmt->fetchColumn();
    }

    public function getInventoryStats(int $businessId): array
    {
        $inStock = $this->db->prepare("
            SELECT COUNT(*)
            FROM products
            WHERE business_id = :business_id
              AND quantity > min_stock_level
              AND status = 'active'
        ");

        $inStock->execute(['business_id' => $businessId]);

        $lowStock = $this->db->prepare("
            SELECT COUNT(*)
            FROM products
            WHERE business_id = :business_id
              AND quantity > 0
              AND quantity <= min_stock_level
              AND status = 'active'
        ");

        $lowStock->execute(['business_id' => $businessId]);

        $outOfStock = $this->db->prepare("
            SELECT COUNT(*)
            FROM products
            WHERE business_id = :business_id
              AND quantity = 0
              AND status = 'active'
        ");

        $outOfStock->execute(['business_id' => $businessId]);

        return [
            'in_stock' => (int) $inStock->fetchColumn(),
            'low_stock' => (int) $lowStock->fetchColumn(),
            'out_of_stock' => (int) $outOfStock->fetchColumn()
        ];
    }

    public function getSalesByCategory(int $businessId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.name, SUM(si.total_price) AS total
            FROM categories c
            JOIN products p ON c.id = p.category_id
            JOIN sale_items si ON p.id = si.product_id
            JOIN sales s ON si.sale_id = s.id
            WHERE p.business_id = :business_id
              AND s.status = 'completed'
            GROUP BY c.id, c.name
            ORDER BY total DESC
        ");

        $stmt->execute(['business_id' => $businessId]);

        return $stmt->fetchAll();
    }
}
