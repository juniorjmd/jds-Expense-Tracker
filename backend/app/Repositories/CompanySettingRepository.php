<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class CompanySettingRepository extends BaseRepository
{
    public function createDefault(int $companyId, ?string $brandingName = null): void
    {
        $this->execute(
            'INSERT INTO company_settings (company_id, currency_code, timezone, date_format, branding_name)
             VALUES (:company_id, :currency_code, :timezone, :date_format, :branding_name)',
            [
                ':company_id' => $companyId,
                ':currency_code' => 'COP',
                ':timezone' => 'America/Bogota',
                ':date_format' => 'Y-m-d',
                ':branding_name' => $brandingName,
            ]
        );
    }

    public function findByCompany(int $companyId): ?array
    {
        return $this->fetchOne(
            'SELECT company_id, currency_code, timezone, date_format, branding_name, created_at, updated_at
             FROM company_settings
             WHERE company_id = :company_id',
            [':company_id' => $companyId]
        );
    }
}
