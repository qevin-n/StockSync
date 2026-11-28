<?php
namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    private function userSelect(): string
    {
        return "
            u.*,
            r.name AS role,
            r.name AS role_name,
            CASE WHEN u.is_active = 1 THEN 'active' ELSE 'inactive' END AS status,
            SUBSTRING_INDEX(u.name, ' ', 1) AS first_name,
            CASE
                WHEN LOCATE(' ', u.name) > 0
                THEN TRIM(SUBSTRING(u.name, LOCATE(' ', u.name) + 1))
                ELSE ''
            END AS last_name,
            NULL AS last_login
        ";
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findByIdWithRole(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT " . $this->userSelect() . ",
                   b.name AS business_name,
                   b.currency
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN businesses b ON u.business_id = b.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByIdAndBusiness(int $id, int $businessId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT " . $this->userSelect() . "
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
              AND u.business_id = :business_id
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $id,
            'business_id' => $businessId,
        ]);
        return $stmt->fetch() ?: null;
    }

    public function findByBusinessWithRole(int $businessId): array
    {
        $stmt = $this->db->prepare("
            SELECT " . $this->userSelect() . "
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.business_id = :business_id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute(['business_id' => $businessId]);
        return $stmt->fetchAll();
    }

    public function getAllWithRole(
        int $businessId,
        int $page = 1,
        int $perPage = 20,
        string $search = '',
        string $status = ''
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['u.business_id = :business_id'];
        $params = ['business_id' => $businessId];

        if ($search !== '') {
            $where[] = '(u.name LIKE :search_name OR u.email LIKE :search_email)';
            $params['search_name'] = '%' . $search . '%';
            $params['search_email'] = '%' . $search . '%';
        }

        if ($status === 'active') {
            $where[] = 'u.is_active = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'u.is_active = 0';
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users u
            WHERE $whereSql
        ");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT " . $this->userSelect() . "
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE $whereSql
            ORDER BY u.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $totalPages = (int) ceil($total / $perPage);
        $rows = $stmt->fetchAll();

        return [
            'data' => $rows,
            'items' => $rows,
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

    // The current users table has no last_login column.
    public function updateLastLogin(int $id): void
    {
        // Intentionally no database update until last_login is supported.
    }

    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->create($data);
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($id, ['password' => $hash]);
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM users WHERE email = :email";
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql . " LIMIT 1");
        $stmt->execute($params);

        return (bool) $stmt->fetch();
    }

    public function getTotalUsers(int $businessId): int
    {
        return $this->count(['business_id' => $businessId]);
    }
}
