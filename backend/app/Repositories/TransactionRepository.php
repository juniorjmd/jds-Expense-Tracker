<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class TransactionRepository extends BaseRepository
{
    public function byEstablishment(int $establishmentId, ?int $companyId = null): array
    {
        $sql = 'SELECT id, company_id, establishment_id, category_id, related_establishment_id, movement_group_id, type, category, description, amount, transaction_date, from_template, created_at, updated_at
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
            'SELECT id, company_id, establishment_id, category_id, related_establishment_id, movement_group_id, type, category, description, amount, transaction_date, from_template, created_at, updated_at
             FROM transactions
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function findByMovementGroup(string $movementGroupId, ?int $companyId = null): array
    {
        $sql = 'SELECT id, company_id, establishment_id, category_id, related_establishment_id, movement_group_id, type, category, description, amount, transaction_date, from_template, created_at, updated_at
                FROM transactions
                WHERE movement_group_id = :movement_group_id';
        $params = [':movement_group_id' => $movementGroupId];

        if ($companyId !== null) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        $sql .= ' ORDER BY id ASC';

        return $this->fetchAll($sql, $params);
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO transactions (company_id, establishment_id, category_id, related_establishment_id, movement_group_id, type, category, description, amount, transaction_date, from_template)
             VALUES (:company_id, :establishment_id, :category_id, :related_establishment_id, :movement_group_id, :type, :category, :description, :amount, :transaction_date, :from_template)',
            [
                ':company_id' => $payload['company_id'],
                ':establishment_id' => $payload['establishment_id'],
                ':category_id' => $payload['category_id'] ?? null,
                ':related_establishment_id' => $payload['related_establishment_id'] ?? null,
                ':movement_group_id' => $payload['movement_group_id'] ?? null,
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

    public function update(int $id, array $payload): array
    {
        $this->execute(
            'UPDATE transactions
             SET establishment_id = :establishment_id,
                 category_id = :category_id,
                 related_establishment_id = :related_establishment_id,
                 movement_group_id = :movement_group_id,
                 type = :type,
                 category = :category,
                 description = :description,
                 amount = :amount,
                 transaction_date = :transaction_date,
                 from_template = :from_template
             WHERE id = :id',
            [
                ':id' => $id,
                ':establishment_id' => $payload['establishment_id'],
                ':category_id' => $payload['category_id'] ?? null,
                ':related_establishment_id' => $payload['related_establishment_id'] ?? null,
                ':movement_group_id' => $payload['movement_group_id'] ?? null,
                ':type' => $payload['type'],
                ':category' => $payload['category'],
                ':description' => $payload['description'] ?? null,
                ':amount' => $payload['amount'],
                ':transaction_date' => $payload['transaction_date'],
                ':from_template' => !empty($payload['from_template']) ? 1 : 0,
            ]
        );

        return $this->find($id) ?? [];
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM transactions WHERE id = :id', [':id' => $id]);
    }

    public function deleteByMovementGroup(string $movementGroupId, ?int $companyId = null): bool
    {
        $sql = 'DELETE FROM transactions WHERE movement_group_id = :movement_group_id';
        $params = [':movement_group_id' => $movementGroupId];

        if ($companyId !== null) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        return $this->execute($sql, $params);
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
                COALESCE(SUM(CASE WHEN t.type IN ("income", "INGRESO_POR_MOVIMIENTO") THEN t.amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN t.type IN ("expense", "SALIDA_POR_MOVIMIENTO") THEN t.amount ELSE 0 END), 0) AS expense
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
