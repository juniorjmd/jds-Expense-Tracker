<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class ActivityLogRepository extends BaseRepository
{
    public function create(array $payload): void
    {
        $this->execute(
            'INSERT INTO activity_logs (
                actor_user_id,
                company_id,
                establishment_id,
                entity_type,
                entity_id,
                action,
                note,
                metadata_json
            ) VALUES (
                :actor_user_id,
                :company_id,
                :establishment_id,
                :entity_type,
                :entity_id,
                :action,
                :note,
                :metadata_json
            )',
            [
                ':actor_user_id' => $payload['actor_user_id'],
                ':company_id' => $payload['company_id'] ?? null,
                ':establishment_id' => $payload['establishment_id'] ?? null,
                ':entity_type' => $payload['entity_type'],
                ':entity_id' => $payload['entity_id'],
                ':action' => $payload['action'],
                ':note' => $payload['note'] ?? null,
                ':metadata_json' => $payload['metadata_json'] ?? null,
            ]
        );
    }

    public function recentByCompany(int $companyId, int $limit = 20): array
    {
        return $this->fetchAll(
            'SELECT
                l.id,
                l.company_id,
                l.establishment_id,
                l.entity_type,
                l.entity_id,
                l.action,
                l.note,
                l.metadata_json,
                l.created_at,
                u.id AS actor_user_id,
                u.full_name AS actor_name,
                u.email AS actor_email
             FROM activity_logs l
             INNER JOIN users u ON u.id = l.actor_user_id
             WHERE l.company_id = :company_id
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT ' . max(1, $limit),
            [':company_id' => $companyId]
        );
    }
}
