<?php
namespace App\Models;

class Setting extends Model {
    protected string $table = 'settings';

    public function getSettingsByBusiness(int $businessId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM settings WHERE business_id = :business_id LIMIT 1"
        );
        $stmt->execute(['business_id' => $businessId]);

        return $stmt->fetch() ?: null;
    }

    public function updateOrCreate(int $businessId, array $data): bool {
        $existing = $this->getSettingsByBusiness($businessId);

        if ($existing) {
            return $this->update((int) $existing['id'], $data);
        }

        $data['business_id'] = $businessId;
        $this->create($data);

        return true;
    }
}
