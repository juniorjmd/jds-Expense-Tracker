<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstablishmentRepository;
use App\Repositories\ExpenseTemplateRepository;
use InvalidArgumentException;

final class ExpenseTemplateService
{
    public function __construct(
        private readonly ExpenseTemplateRepository $repository = new ExpenseTemplateRepository(),
        private readonly TransactionService $transactionService = new TransactionService(),
        private readonly EstablishmentRepository $establishmentRepository = new EstablishmentRepository(),
        private readonly ActivityLogService $activityLogs = new ActivityLogService()
    ) {
    }

    public function listByEstablishment(array $actor, int $establishmentId): array
    {
        $companyId = $this->assertEstablishment($actor, $establishmentId);

        return array_map([$this, 'mapTemplate'], $this->repository->byEstablishment($establishmentId, $companyId));
    }

    public function create(array $actor, int $establishmentId, array $payload): array
    {
        $companyId = $this->assertEstablishment($actor, $establishmentId);

        $category = trim((string) ($payload['category'] ?? ''));
        if ($category === '') {
            throw new InvalidArgumentException('La categoria es obligatoria.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $created = $this->repository->create([
            'company_id' => $companyId,
            'establishment_id' => $establishmentId,
            'category' => $category,
            'description' => trim((string) ($payload['description'] ?? '')),
            'amount' => round($amount, 2),
        ]);

        $this->activityLogs->log(
            $actor,
            'expense_template',
            (string) $created['id'],
            'expense_template_created',
            (int) $created['company_id'],
            (int) $created['establishment_id'],
            'Gasto predeterminado creado.',
            [
                'category' => (string) $created['category'],
                'amount' => (float) $created['amount'],
            ]
        );

        return $this->mapTemplate($created);
    }

    public function apply(array $actor, int $id): array
    {
        $template = $this->repository->find($id);
        if ($template === null) {
            throw new InvalidArgumentException('El gasto predeterminado no existe.');
        }

        if (($actor['role'] ?? '') !== 'superusuario' && (int) ($template['company_id'] ?? 0) !== (int) ($actor['company_id'] ?? 0)) {
            throw new InvalidArgumentException('No tienes acceso a este gasto predeterminado.');
        }

        $applied = $this->transactionService->create($actor, (int) $template['establishment_id'], [
            'type' => 'expense',
            'category' => (string) $template['category'],
            'description' => (string) ($template['description'] ?? ''),
            'amount' => (float) $template['amount'],
            'transaction_date' => date('Y-m-d'),
            'from_template' => true,
        ]);

        $this->activityLogs->log(
            $actor,
            'expense_template',
            (string) $template['id'],
            'expense_template_applied',
            (int) ($template['company_id'] ?? 0),
            (int) ($template['establishment_id'] ?? 0),
            'Gasto predeterminado aplicado como transaccion.',
            [
                'transactionId' => $applied['id'] ?? null,
                'amount' => (float) ($template['amount'] ?? 0),
            ]
        );

        return $applied;
    }

    public function delete(array $actor, int $id): bool
    {
        $template = $this->repository->find($id);
        if ($id < 1 || $template === null) {
            return false;
        }

        if (($actor['role'] ?? '') !== 'superusuario' && (int) ($template['company_id'] ?? 0) !== (int) ($actor['company_id'] ?? 0)) {
            return false;
        }

        $this->activityLogs->log(
            $actor,
            'expense_template',
            (string) $template['id'],
            'expense_template_deleted',
            (int) ($template['company_id'] ?? 0),
            (int) ($template['establishment_id'] ?? 0),
            'Gasto predeterminado eliminado.',
            [
                'category' => (string) ($template['category'] ?? ''),
                'amount' => (float) ($template['amount'] ?? 0),
            ]
        );

        return $this->repository->delete($id);
    }

    private function assertEstablishment(array $actor, int $establishmentId): int
    {
        $companyId = ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0);
        $establishment = $establishmentId > 0 ? $this->establishmentRepository->find($establishmentId, date('Y-m'), $companyId) : null;
        if ($establishment === null) {
            throw new InvalidArgumentException('El establecimiento no existe.');
        }

        return (int) $establishment['company_id'];
    }

    private function mapTemplate(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'companyId' => (string) $row['company_id'],
            'establishmentId' => (string) $row['establishment_id'],
            'category' => (string) $row['category'],
            'description' => (string) ($row['description'] ?? ''),
            'amount' => (float) $row['amount'],
            'createdAt' => (string) $row['created_at'],
        ];
    }
}
