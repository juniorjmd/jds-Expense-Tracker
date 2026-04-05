<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstablishmentRepository;
use InvalidArgumentException;

final class EstablishmentService
{
    public function __construct(
        private readonly EstablishmentRepository $repository = new EstablishmentRepository(),
        private readonly ActivityLogService $activityLogs = new ActivityLogService()
    ) {
    }

    public function list(array $actor, ?string $month = null): array
    {
        $normalizedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $month) === 1 ? (string) $month : date('Y-m');
        $companyId = ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0);

        return array_map([$this, 'mapEstablishment'], $this->repository->all($normalizedMonth, $companyId));
    }

    public function show(array $actor, int $id, ?string $month = null): ?array
    {
        $normalizedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $month) === 1 ? (string) $month : date('Y-m');
        $companyId = ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0);
        $establishment = $this->repository->find($id, $normalizedMonth, $companyId);

        return $establishment !== null ? $this->mapEstablishment($establishment) : null;
    }

    public function create(array $actor, array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('El nombre del establecimiento es obligatorio.');
        }

        $companyId = ($actor['role'] ?? '') === 'superusuario'
            ? (int) ($payload['companyId'] ?? 0)
            : (int) ($actor['company_id'] ?? 0);

        if ($companyId < 1) {
            throw new InvalidArgumentException('La empresa del establecimiento es obligatoria.');
        }

        $created = $this->repository->create([
            'company_id' => $companyId,
            'name' => $name,
            'description' => trim((string) ($payload['description'] ?? '')),
        ], date('Y-m'));

        $this->activityLogs->log(
            $actor,
            'establishment',
            (string) $created['id'],
            'establishment_created',
            (int) $created['company_id'],
            (int) $created['id'],
            'Establecimiento creado.',
            [
                'name' => (string) $created['name'],
            ]
        );

        return $this->mapEstablishment($created);
    }

    public function delete(array $actor, int $id): bool
    {
        $companyId = ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0);
        $existing = $id > 0 ? $this->repository->find($id, date('Y-m'), $companyId) : null;
        if ($id < 1 || $existing === null) {
            return false;
        }

        $this->activityLogs->log(
            $actor,
            'establishment',
            (string) $existing['id'],
            'establishment_deleted',
            (int) $existing['company_id'],
            (int) $existing['id'],
            'Establecimiento eliminado.',
            [
                'name' => (string) ($existing['name'] ?? ''),
            ]
        );

        return $this->repository->delete($id, $companyId);
    }

    private function mapEstablishment(array $row): array
    {
        $income = (float) ($row['income'] ?? 0);
        $expense = (float) ($row['expense'] ?? 0);

        return [
            'id' => (string) $row['id'],
            'companyId' => (string) $row['company_id'],
            'companyName' => (string) ($row['company_name'] ?? ''),
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
