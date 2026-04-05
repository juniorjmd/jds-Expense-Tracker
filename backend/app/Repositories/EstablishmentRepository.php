<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class EstablishmentRepository extends BaseRepository
{
    public function all(string $month): array
    {
        return $this->fetchAll(
            'SELECT
                e.id,
                e.name,
                e.description,
                e.created_at,
                COUNT(t.id) AS transaction_count,
                COALESCE(SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN t.type = "expense" THEN t.amount ELSE 0 END), 0) AS expense
             FROM establishments e
             LEFT JOIN transactions t
                ON t.establishment_id = e.id
               AND DATE_FORMAT(t.transaction_date, "%Y-%m") = :month
             GROUP BY e.id, e.name, e.description, e.created_at
             ORDER BY e.created_at DESC, e.id DESC',
            [':month' => $month]
        );
    }

    public function find(int $id, string $month): ?array
    {
        return $this->fetchOne(
            'SELECT
                e.id,
                e.name,
                e.description,
                e.created_at,
                COUNT(t.id) AS transaction_count,
                COALESCE(SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN t.type = "expense" THEN t.amount ELSE 0 END), 0) AS expense
             FROM establishments e
             LEFT JOIN transactions t
                ON t.establishment_id = e.id
               AND DATE_FORMAT(t.transaction_date, "%Y-%m") = :month
             WHERE e.id = :id
             GROUP BY e.id, e.name, e.description, e.created_at',
            [
                ':id' => $id,
                ':month' => $month,
            ]
        );
    }

    public function create(array $payload, string $month): array
    {
        $this->execute(
            'INSERT INTO establishments (name, description) VALUES (:name, :description)',
            [
                ':name' => $payload['name'],
                ':description' => $payload['description'] ?? null,
            ]
        );

        return $this->find((int) $this->db->lastInsertId(), $month) ?? [];
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM establishments WHERE id = :id', [':id' => $id]);
    }
}
