<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class TransactionRepository extends BaseRepository
{
    public function byEstablishment(int $establishmentId, ?int $companyId = null): array
    {
        $sql = 'SELECT id, company_id, establishment_id, type, category, description, amount, transaction_date, from_template, created_at
             FROM transactions
             WHERE establishment_id = :establishment_id';
        $params = [':establishment_id' => $establishmentId];
        if ($companyId !== null) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        $sql .= ' ORDER BY transaction_date DESC, id DESC';

        return $this->fetchAll($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, company_id, establishment_id, type, category, description, amount, transaction_date, from_template, created_at
             FROM transactions
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO transactions (company_id, establishment_id, type, category, description, amount, transaction_date, from_template)
             VALUES (:company_id, :establishment_id, :type, :category, :description, :amount, :transaction_date, :from_template)',
            [
                ':company_id' => $payload['company_id'],
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

    public function monthlyTotals(string $month, ?int $companyId = null): array
    {
        $sql = 'SELECT
                COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END), 0) AS expense
             FROM transactions
             WHERE DATE_FORMAT(transaction_date, "%Y-%m") = :month';
        $params = [':month' => $month];
        if ($companyId !== null) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        return $this->fetchOne($sql, $params) ?? ['income' => 0, 'expense' => 0];
    }

    public function monthlyBreakdown(string $month, ?int $companyId = null): array
    {
        $sql = 'SELECT
                e.id,
                e.company_id,
                c.name AS company_name,
                e.name,
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

        $sql .= ' GROUP BY e.id, e.company_id, c.name, e.name
             ORDER BY c.name ASC, e.name ASC';

        return $this->fetchAll(
            $sql,
            $params
        );
    }
}
