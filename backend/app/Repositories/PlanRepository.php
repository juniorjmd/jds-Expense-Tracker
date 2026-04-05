<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class PlanRepository extends BaseRepository
{
    public function findDefault(): ?array
    {
        return $this->fetchOne(
            'SELECT id, code, name, description, monthly_price, establishments_limit, users_limit
             FROM plans
             WHERE is_default = 1 AND is_active = 1
             ORDER BY id ASC
             LIMIT 1'
        );
    }
}
