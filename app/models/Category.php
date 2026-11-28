<?php
namespace App\Models;

class Category extends Model {
    protected string $table = 'categories';

    public function findByNameAndBusiness(string $name, int $businessId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE name = :name AND business_id = :business_id LIMIT 1");
        $stmt->execute(['name' => $name, 'business_id' => $businessId]);
        return $stmt->fetch() ?: null;
    }

    public function getProductCount(int $categoryId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }

    public function getAllByBusiness(int $businessId): array {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            WHERE c.business_id = :business_id
            GROUP BY c.id
            ORDER BY c.name
        ");
        $stmt->execute(['business_id' => $businessId]);
        return $stmt->fetchAll();
    }
}
