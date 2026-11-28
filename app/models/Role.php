<?php
namespace App\Models;

class Role extends Model {
    protected string $table = 'roles';

    public function findAll(): array {
        return $this->all('id ASC');
    }

    public function findByName(string $name): ?array {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() ?: null;
    }
}
