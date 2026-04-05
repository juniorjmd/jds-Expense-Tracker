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
                    COUNT(DISTINCT e.id) AS establishments_count,
                    COUNT(DISTINCT u.id) AS users_count
             FROM companies c
             LEFT JOIN establishments e ON e.company_id = c.id
             LEFT JOIN users u ON u.company_id = c.id AND u.role <> "superusuario"
             GROUP BY c.id, c.name, c.description, c.created_at
             ORDER BY c.created_at DESC, c.id DESC'
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
                    COUNT(DISTINCT e.id) AS establishments_count,
                    COUNT(DISTINCT u.id) AS users_count
             FROM companies c
             LEFT JOIN establishments e ON e.company_id = c.id
             LEFT JOIN users u ON u.company_id = c.id AND u.role <> "superusuario"
             WHERE c.id = :id
             GROUP BY c.id, c.name, c.description, c.created_at',
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
}
