<?php
namespace App\Models;

use App\Config\Database;
use PDO;

abstract class Model {
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected ?PDO $db = null;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    public function commit(): bool {
        return $this->db->commit();
    }

    public function rollBack(): bool {
        return $this->db->rollBack();
    }

    public function inTransaction(): bool {
        return $this->db->inTransaction();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByIdAndBusiness(int $id, int $businessId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND business_id = :business_id LIMIT 1");
        $stmt->execute(['id' => $id, 'business_id' => $businessId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByBusiness(int $businessId, string $orderBy = 'created_at DESC'): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE business_id = :business_id ORDER BY {$orderBy}");
        $stmt->execute(['business_id' => $businessId]);
        return $stmt->fetchAll();
    }

    public function all(string $orderBy = 'id DESC'): array {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $fields = array_keys($data);
        $columns = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $fields);
        $data['id'] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id");
        return $stmt->execute($data);
    }

    public function updateByBusiness(int $id, int $businessId, array $data): bool {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $fields);
        $data['id'] = $id;
        $data['business_id'] = $businessId;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id AND business_id = :business_id");
        return $stmt->execute($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function deleteByBusiness(int $id, int $businessId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id AND business_id = :business_id");
        return $stmt->execute(['id' => $id, 'business_id' => $businessId]);
    }

    public function count(array $where = []): int {
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = :{$key}";
            }
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} {$whereClause}");
            $stmt->execute($where);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        }
        return (int) $stmt->fetchColumn();
    }

    public function paginate(int $businessId, int $page = 1, int $perPage = 20, string $orderBy = 'created_at DESC'): array {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE business_id = :business_id ORDER BY {$orderBy} LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':business_id', $businessId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE business_id = :business_id");
        $totalStmt->execute(['business_id' => $businessId]);
        $total = (int) $totalStmt->fetchColumn();

        return [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => (int) ceil($total / $perPage),
            'hasPrev' => $page > 1,
            'hasNext' => $page < (int) ceil($total / $perPage),
        ];
    }

    public function search(int $businessId, string $query, array $searchableFields, int $page = 1, int $perPage = 20): array {
        $conditions = [];
        foreach ($searchableFields as $field) {
            $conditions[] = "{$field} LIKE :search";
        }
        $whereClause = 'business_id = :business_id AND (' . implode(' OR ', $conditions) . ')';
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':business_id', $businessId, PDO::PARAM_INT);
        $searchParam = "%{$query}%";
        foreach ($searchableFields as $field) {
            $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->bindValue(':business_id', $businessId, PDO::PARAM_INT);
        $countStmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => (int) ceil($total / $perPage),
            'hasPrev' => $page > 1,
            'hasNext' => $page < (int) ceil($total / $perPage),
        ];
    }
}
