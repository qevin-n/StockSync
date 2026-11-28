<?php
namespace App\Models;

class Business extends Model {
    protected string $table = 'businesses';

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM businesses WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM businesses WHERE slug = :slug LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}
