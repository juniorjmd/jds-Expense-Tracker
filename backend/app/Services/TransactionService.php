<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\TransactionRepository;
use InvalidArgumentException;

final class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $repository = new TransactionRepository()
    ) {
    }

    public function list(?string $type = null, ?string $month = null): array
    {
        $normalizedType = $type !== null ? strtolower(trim($type)) : null;
        if (!in_array($normalizedType, [null, '', 'income', 'expense'], true)) {
            $normalizedType = null;
        }

        $normalizedMonth = $month !== null ? trim($month) : null;
        if ($normalizedMonth !== null && preg_match('/^\d{4}-\d{2}$/', $normalizedMonth) !== 1) {
            $normalizedMonth = null;
        }

        return $this->repository->all($normalizedType ?: null, $normalizedMonth);
    }

    public function create(array $payload): array
    {
        $type = strtolower(trim((string) ($payload['type'] ?? 'expense')));
        if (!in_array($type, ['income', 'expense'], true)) {
            throw new InvalidArgumentException('El tipo debe ser income o expense.');
        }

        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('El titulo es obligatorio.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $transactionDate = (string) ($payload['transaction_date'] ?? date('Y-m-d'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate) !== 1) {
            throw new InvalidArgumentException('La fecha debe tener formato YYYY-MM-DD.');
        }

        $categoryId = $payload['category_id'] ?? null;
        if ($categoryId !== null && $categoryId !== '' && (!is_numeric($categoryId) || (int) $categoryId < 1)) {
            throw new InvalidArgumentException('La categoria es invalida.');
        }

        return $this->repository->create([
            'category_id' => $categoryId !== null && $categoryId !== '' ? (int) $categoryId : null,
            'type' => $type,
            'title' => $title,
            'amount' => round($amount, 2),
            'transaction_date' => $transactionDate,
            'notes' => isset($payload['notes']) ? trim((string) $payload['notes']) : null,
        ]);
    }

    public function delete(int $id): bool
    {
        if ($id < 1 || $this->repository->find($id) === null) {
            return false;
        }

        return $this->repository->delete($id);
    }

    public function summary(string $month): array
    {
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            $month = date('Y-m');
        }

        return $this->repository->summary($month);
    }
}
