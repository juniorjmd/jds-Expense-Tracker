<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class TransactionRepository extends BaseRepository
{
    public function byEstablishment(int $establishmentId): array
    {
        return $this->fetchAll(
            'SELECT id, establishment_id, type, category, description, amount, transaction_date, from_template, created_at
             FROM transactions
             WHERE establishment_id = :establishment_id
             ORDER BY transaction_date DESC, id DESC',
            [':establishment_id' => $establishmentId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, establishment_id, type, category, description, amount, transaction_date, from_template, created_at
             FROM transactions
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO transactions (establishment_id, type, category, description, amount, transaction_date, from_template)
             VALUES (:establishment_id, :type, :category, :description, :amount, :transaction_date, :from_template)',
            [
                ':establishment_id' => $payload['establishment_id'],
                ':type' => $payload['type'],
                ':category' => $payload['category'],
                ':description' => $payload['description'] ?? null,
                ':amount' => $payload['amount'],
                ':transaction_date' => $payload['transaction_date'],
                ':from_template' => !empty($payload['from_template']) ? 1 : 0,
            ]
        );

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM transactions WHERE id = :id', [':id' => $id]);
    }

    public function availableMonths(): array
    {
        return $this->fetchAll(
            'SELECT DISTINCT DATE_FORMAT(transaction_date, "%Y-%m") AS month
             FROM transactions
             ORDER BY month DESC'
        );
    }

    public function monthlyTotals(string $month): array
    {
        return $this->fetchOne(
            'SELECT
                COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END), 0) AS expense
             FROM transactions
             WHERE DATE_FORMAT(transaction_date, "%Y-%m") = :month',
            [':month' => $month]
        ) ?? ['income' => 0, 'expense' => 0];
    }

    public function monthlyBreakdown(string $month): array
    {
        return $this->fetchAll(
            'SELECT
                e.id,
                e.name,
                COALESCE(SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN t.type = "expense" THEN t.amount ELSE 0 END), 0) AS expense
             FROM establishments e
             LEFT JOIN transactions t
                ON t.establishment_id = e.id
               AND DATE_FORMAT(t.transaction_date, "%Y-%m") = :month
             GROUP BY e.id, e.name
             ORDER BY e.name ASC',
            [':month' => $month]
        );
    }
}
