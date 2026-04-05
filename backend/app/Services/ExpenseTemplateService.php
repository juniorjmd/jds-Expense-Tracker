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
        private readonly EstablishmentRepository $establishmentRepository = new EstablishmentRepository()
    ) {
    }

    public function listByEstablishment(int $establishmentId): array
    {
        $this->assertEstablishment($establishmentId);

        return array_map([$this, 'mapTemplate'], $this->repository->byEstablishment($establishmentId));
    }

    public function create(int $establishmentId, array $payload): array
    {
        $this->assertEstablishment($establishmentId);

        $category = trim((string) ($payload['category'] ?? ''));
        if ($category === '') {
            throw new InvalidArgumentException('La categoria es obligatoria.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        return $this->mapTemplate($this->repository->create([
            'establishment_id' => $establishmentId,
            'category' => $category,
            'description' => trim((string) ($payload['description'] ?? '')),
            'amount' => round($amount, 2),
        ]));
    }

    public function apply(int $id): array
    {
        $template = $this->repository->find($id);
        if ($template === null) {
            throw new InvalidArgumentException('El gasto predeterminado no existe.');
        }

        return $this->transactionService->create((int) $template['establishment_id'], [
            'type' => 'expense',
            'category' => (string) $template['category'],
            'description' => (string) ($template['description'] ?? ''),
            'amount' => (float) $template['amount'],
            'transaction_date' => date('Y-m-d'),
            'from_template' => true,
        ]);
    }

    public function delete(int $id): bool
    {
        if ($id < 1 || $this->repository->find($id) === null) {
            return false;
        }

        return $this->repository->delete($id);
    }

    private function assertEstablishment(int $establishmentId): void
    {
        if ($establishmentId < 1 || $this->establishmentRepository->find($establishmentId, date('Y-m')) === null) {
            throw new InvalidArgumentException('El establecimiento no existe.');
        }
    }

    private function mapTemplate(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'establishmentId' => (string) $row['establishment_id'],
            'category' => (string) $row['category'],
            'description' => (string) ($row['description'] ?? ''),
            'amount' => (float) $row['amount'],
            'createdAt' => (string) $row['created_at'],
        ];
    }
}
