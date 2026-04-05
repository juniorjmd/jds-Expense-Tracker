<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class CompanySubscriptionRepository extends BaseRepository
{
    public function createDefault(int $companyId, int $planId): void
    {
        $this->execute(
            'INSERT INTO company_subscriptions (company_id, plan_id, status, starts_at, ends_at)
             VALUES (:company_id, :plan_id, :status, :starts_at, :ends_at)',
            [
                ':company_id' => $companyId,
                ':plan_id' => $planId,
                ':status' => 'active',
                ':starts_at' => date('Y-m-d'),
                ':ends_at' => null,
            ]
        );
    }

    public function findCurrentByCompany(int $companyId): ?array
    {
        return $this->fetchOne(
            'SELECT
                cs.id,
                cs.company_id,
                cs.plan_id,
                cs.status,
                cs.starts_at,
                cs.ends_at,
                p.code AS plan_code,
                p.name AS plan_name,
                p.monthly_price,
                p.establishments_limit,
                p.users_limit
             FROM company_subscriptions cs
             INNER JOIN plans p ON p.id = cs.plan_id
             WHERE cs.company_id = :company_id
             ORDER BY cs.created_at DESC, cs.id DESC
             LIMIT 1',
            [':company_id' => $companyId]
        );
    }
}
