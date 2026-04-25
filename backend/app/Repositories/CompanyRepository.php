<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class CompanyRepository extends BaseRepository
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT c.id, c.name, c.description, c.created_at,
                    cs.status AS subscription_status,
                    p.code AS plan_code,
                    p.name AS plan_name,
                    cfg.currency_code,
                    cfg.timezone,
                    (SELECT COUNT(*) FROM establishments e WHERE e.company_id = c.id) AS establishments_count,
                    (SELECT COUNT(DISTINCT u.id)
                     FROM users u
                     LEFT JOIN company_admin_users cau ON cau.user_id = u.id
                     WHERE u.role <> "superusuario"
                       AND (u.company_id = c.id OR cau.company_id = c.id)) AS users_count
             FROM companies c
             LEFT JOIN company_subscriptions cs ON cs.company_id = c.id
             LEFT JOIN plans p ON p.id = cs.plan_id
             LEFT JOIN company_settings cfg ON cfg.company_id = c.id
             ORDER BY c.created_at DESC, c.id DESC'
        );
    }

    public function assignedToAdmin(int $userId): array
    {
        return $this->fetchAll(
            'SELECT c.id, c.name, c.description, c.created_at,
                    cs.status AS subscription_status,
                    p.code AS plan_code,
                    p.name AS plan_name,
                    cfg.currency_code,
                    cfg.timezone,
                    (SELECT COUNT(*) FROM establishments e WHERE e.company_id = c.id) AS establishments_count,
                    (SELECT COUNT(DISTINCT u.id)
                     FROM users u
                     LEFT JOIN company_admin_users cau2 ON cau2.user_id = u.id
                     WHERE u.role <> "superusuario"
                       AND (u.company_id = c.id OR cau2.company_id = c.id)) AS users_count
             FROM companies c
             INNER JOIN company_admin_users cau ON cau.company_id = c.id
             LEFT JOIN company_subscriptions cs ON cs.company_id = c.id
             LEFT JOIN plans p ON p.id = cs.plan_id
             LEFT JOIN company_settings cfg ON cfg.company_id = c.id
             WHERE cau.user_id = :user_id
             ORDER BY c.created_at DESC, c.id DESC',
            [':user_id' => $userId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, name, description, created_at FROM companies WHERE id = :id',
            [':id' => $id]
        );
    }

    public function findWithCounts(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT c.id, c.name, c.description, c.created_at,
                    cs.status AS subscription_status,
                    p.code AS plan_code,
                    p.name AS plan_name,
                    cfg.currency_code,
                    cfg.timezone,
                    cfg.date_format,
                    cfg.branding_name,
                    (SELECT COUNT(*) FROM establishments e WHERE e.company_id = c.id) AS establishments_count,
                    (SELECT COUNT(DISTINCT u.id)
                     FROM users u
                     LEFT JOIN company_admin_users cau ON cau.user_id = u.id
                     WHERE u.role <> "superusuario"
                       AND (u.company_id = c.id OR cau.company_id = c.id)) AS users_count
             FROM companies c
             LEFT JOIN company_subscriptions cs ON cs.company_id = c.id
             LEFT JOIN plans p ON p.id = cs.plan_id
             LEFT JOIN company_settings cfg ON cfg.company_id = c.id
             WHERE c.id = :id
             GROUP BY c.id, c.name, c.description, c.created_at, cs.status, p.code, p.name, cfg.currency_code, cfg.timezone, cfg.date_format, cfg.branding_name',
            [':id' => $id]
        );
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO companies (name, description) VALUES (:name, :description)',
            [
                ':name' => $payload['name'],
                ':description' => $payload['description'] ?? null,
            ]
        );

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }

    public function assignAdmin(int $companyId, int $userId): void
    {
        $this->execute(
            'INSERT INTO company_admin_users (user_id, company_id) VALUES (:user_id, :company_id)',
            [
                ':user_id' => $userId,
                ':company_id' => $companyId,
            ]
        );
    }

    public function isAdminAssigned(int $companyId, int $userId): bool
    {
        return $this->fetchOne(
            'SELECT user_id FROM company_admin_users WHERE company_id = :company_id AND user_id = :user_id',
            [
                ':company_id' => $companyId,
                ':user_id' => $userId,
            ]
        ) !== null;
    }
}
