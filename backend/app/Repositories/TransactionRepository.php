<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;
use App\Core\Database\QueryBuilder;

final class TransactionRepository extends BaseRepository
{
    public function all(?string $type = null, ?string $month = null): array
    {
        $builder = (new QueryBuilder())
            ->table('transactions')
            ->select([
                'id',
                'category_id',
                'type',
                'title',
                'amount',
                'transaction_date',
                'notes',
                'created_at',
            ])
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC');

        if ($type !== null && $type !== '') {
            $builder->where('type', '=', $type);
        }

        if ($month !== null && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $builder->where('transaction_date', '>=', $month . '-01');
            $builder->where('transaction_date', '<=', date('Y-m-t', strtotime($month . '-01')));
        }

        return $this->fetchAll($builder->toSql(), $builder->getParams());
    }

    public function create(array $payload): array
    {
        $sql = 'INSERT INTO transactions (category_id, type, title, amount, transaction_date, notes)
                VALUES (:category_id, :type, :title, :amount, :transaction_date, :notes)';

        $this->execute($sql, [
            ':category_id' => $payload['category_id'] ?: null,
            ':type' => $payload['type'],
            ':title' => $payload['title'],
            ':amount' => $payload['amount'],
            ':transaction_date' => $payload['transaction_date'],
            ':notes' => $payload['notes'] ?? null,
        ]);

        $id = (int) $this->db->lastInsertId();

        return $this->find($id) ?? [];
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, category_id, type, title, amount, transaction_date, notes, created_at
             FROM transactions
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            'DELETE FROM transactions WHERE id = :id',
            [':id' => $id]
        );
    }

    public function summary(string $month): array
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $result = $this->fetchOne(
            'SELECT
                COALESCE(SUM(CASE WHEN type = "income" THEN amount END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount END), 0) AS total_expense
             FROM transactions
             WHERE transaction_date BETWEEN :start_date AND :end_date',
            [
                ':start_date' => $startDate,
                ':end_date' => $endDate,
            ]
        );

        $income = (float) ($result['total_income'] ?? 0);
        $expense = (float) ($result['total_expense'] ?? 0);

        return [
            'month' => $month,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }
}
