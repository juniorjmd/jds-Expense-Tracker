<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class CompanyAccessLogRepository extends BaseRepository
{
    public function create(array $payload): void
    {
        $this->execute(
            'INSERT INTO company_access_logs (actor_user_id, company_id, action, note)
             VALUES (:actor_user_id, :company_id, :action, :note)',
            [
                ':actor_user_id' => $payload['actor_user_id'],
                ':company_id' => $payload['company_id'],
                ':action' => $payload['action'] ?? 'view_company_overview',
                ':note' => $payload['note'] ?? null,
            ]
        );
    }

    public function recentByCompany(int $companyId, int $limit = 15): array
    {
        return $this->fetchAll(
            'SELECT
                l.id,
                l.company_id,
                l.actor_user_id,
                u.full_name AS actor_name,
                u.email AS actor_email,
                l.action,
                l.note,
                l.created_at
             FROM company_access_logs l
             INNER JOIN users u ON u.id = l.actor_user_id
             WHERE l.company_id = :company_id
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT ' . max(1, $limit),
            [':company_id' => $companyId]
        );
    }
}
