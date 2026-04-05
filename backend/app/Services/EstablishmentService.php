<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstablishmentRepository;
use InvalidArgumentException;

final class EstablishmentService
{
    public function __construct(
        private readonly EstablishmentRepository $repository = new EstablishmentRepository()
    ) {
    }

    public function list(?string $month = null): array
    {
        $normalizedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $month) === 1 ? (string) $month : date('Y-m');
        return array_map([$this, 'mapEstablishment'], $this->repository->all($normalizedMonth));
    }

    public function show(int $id, ?string $month = null): ?array
    {
        $normalizedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $month) === 1 ? (string) $month : date('Y-m');
        $establishment = $this->repository->find($id, $normalizedMonth);

        return $establishment !== null ? $this->mapEstablishment($establishment) : null;
    }

    public function create(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('El nombre del establecimiento es obligatorio.');
        }

        return $this->mapEstablishment($this->repository->create([
            'name' => $name,
            'description' => trim((string) ($payload['description'] ?? '')),
        ], date('Y-m')));
    }

    public function delete(int $id): bool
    {
        if ($id < 1 || $this->repository->find($id, date('Y-m')) === null) {
            return false;
        }

        return $this->repository->delete($id);
    }

    private function mapEstablishment(array $row): array
    {
        $income = (float) ($row['income'] ?? 0);
        $expense = (float) ($row['expense'] ?? 0);

        return [
            'id' => (string) $row['id'],
            'name' => (string) $row['name'],
            'description' => (string) ($row['description'] ?? ''),
            'createdAt' => (string) $row['created_at'],
            'transactionCount' => (int) ($row['transaction_count'] ?? 0),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }
}
