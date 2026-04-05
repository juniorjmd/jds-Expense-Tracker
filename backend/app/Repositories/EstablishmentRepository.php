<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class EstablishmentRepository extends BaseRepository
{
    public function all(string $month, ?int $companyId = null): array
    {
        $sql = 'SELECT
                e.id,
                e.company_id,
                c.name AS company_name,
                e.name,
                e.description,
                e.created_at,
                COUNT(t.id) AS transaction_count,
                COALESCE(SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN t.type = "expense" THEN t.amount ELSE 0 END), 0) AS expense
             FROM establishments e
             INNER JOIN companies c ON c.id = e.company_id
             LEFT JOIN transactions t
                ON t.establishment_id = e.id
               AND DATE_FORMAT(t.transaction_date, "%Y-%m") = :month';

        $params = [':month' => $month];
        if ($companyId !== null) {
            $sql .= ' WHERE e.company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        $sql .= ' GROUP BY e.id, e.company_id, c.name, e.name, e.description, e.created_at
                  ORDER BY e.created_at DESC, e.id DESC';

        return $this->fetchAll($sql, $params);
    }

    public function find(int $id, string $month, ?int $companyId = null): ?array
    {
        $sql = 'SELECT
                e.id,
                e.company_id,
                c.name AS company_name,
                e.name,
                e.description,
                e.created_at,
                COUNT(t.id) AS transaction_count,
                COALESCE(SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN t.type = "expense" THEN t.amount ELSE 0 END), 0) AS expense
             FROM establishments e
             INNER JOIN companies c ON c.id = e.company_id
             LEFT JOIN transactions t
                ON t.establishment_id = e.id
               AND DATE_FORMAT(t.transaction_date, "%Y-%m") = :month
             WHERE e.id = :id';

        $params = [
            ':id' => $id,
            ':month' => $month,
        ];

        if ($companyId !== null) {
            $sql .= ' AND e.company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        $sql .= ' GROUP BY e.id, e.company_id, c.name, e.name, e.description, e.created_at';

        return $this->fetchOne($sql, $params);
    }

    public function create(array $payload, string $month): array
    {
        $this->execute(
            'INSERT INTO establishments (company_id, name, description) VALUES (:company_id, :name, :description)',
            [
                ':company_id' => $payload['company_id'],
                ':name' => $payload['name'],
                ':description' => $payload['description'] ?? null,
            ]
        );

        return $this->find((int) $this->db->lastInsertId(), $month, (int) $payload['company_id']) ?? [];
    }

    public function delete(int $id, ?int $companyId = null): bool
    {
        $sql = 'DELETE FROM establishments WHERE id = :id';
        $params = [':id' => $id];

        if ($companyId !== null) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        return $this->execute($sql, $params);
    }

    public function companyIdByEstablishment(int $id): ?int
    {
        $row = $this->fetchOne('SELECT company_id FROM establishments WHERE id = :id', [':id' => $id]);
        return $row !== null ? (int) $row['company_id'] : null;
    }
}
