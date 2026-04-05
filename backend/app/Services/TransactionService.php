<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstablishmentRepository;
use App\Repositories\TransactionRepository;
use InvalidArgumentException;

final class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $repository = new TransactionRepository(),
        private readonly EstablishmentRepository $establishmentRepository = new EstablishmentRepository(),
        private readonly ActivityLogService $activityLogs = new ActivityLogService()
    ) {
    }

    public function listByEstablishment(array $actor, int $establishmentId): array
    {
        $companyId = $this->assertEstablishment($actor, $establishmentId);

        return array_map([$this, 'mapTransaction'], $this->repository->byEstablishment($establishmentId, $companyId));
    }

    public function create(array $actor, int $establishmentId, array $payload): array
    {
        $companyId = $this->assertEstablishment($actor, $establishmentId);

        $type = strtolower(trim((string) ($payload['type'] ?? 'expense')));
        if (!in_array($type, ['income', 'expense'], true)) {
            throw new InvalidArgumentException('El tipo debe ser income o expense.');
        }

        $category = trim((string) ($payload['category'] ?? ''));
        if ($category === '') {
            throw new InvalidArgumentException('La categoria es obligatoria.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $transactionDate = (string) ($payload['transaction_date'] ?? date('Y-m-d'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate) !== 1) {
            throw new InvalidArgumentException('La fecha debe tener formato YYYY-MM-DD.');
        }

        $created = $this->repository->create([
            'company_id' => $companyId,
            'establishment_id' => $establishmentId,
            'type' => $type,
            'category' => $category,
            'description' => trim((string) ($payload['description'] ?? '')),
            'amount' => round($amount, 2),
            'transaction_date' => $transactionDate,
            'from_template' => !empty($payload['from_template']),
        ]);

        $this->activityLogs->log(
            $actor,
            'transaction',
            (string) $created['id'],
            'transaction_created',
            (int) $created['company_id'],
            (int) $created['establishment_id'],
            'Movimiento registrado en el establecimiento.',
            [
                'type' => (string) $created['type'],
                'amount' => (float) $created['amount'],
                'category' => (string) $created['category'],
            ]
        );

        return $this->mapTransaction($created);
    }

    public function delete(array $actor, int $id): bool
    {
        $transaction = $this->repository->find($id);
        if ($id < 1 || $transaction === null) {
            return false;
        }

        if (($actor['role'] ?? '') !== 'superusuario' && (int) ($transaction['company_id'] ?? 0) !== (int) ($actor['company_id'] ?? 0)) {
            return false;
        }

        $this->activityLogs->log(
            $actor,
            'transaction',
            (string) $transaction['id'],
            'transaction_deleted',
            (int) ($transaction['company_id'] ?? 0),
            (int) ($transaction['establishment_id'] ?? 0),
            'Movimiento eliminado.',
            [
                'type' => (string) ($transaction['type'] ?? ''),
                'amount' => (float) ($transaction['amount'] ?? 0),
                'category' => (string) ($transaction['category'] ?? ''),
            ]
        );

        return $this->repository->delete($id);
    }

    public function summary(array $actor, string $month, ?int $selectedCompanyId = null): array
    {
        $normalizedMonth = preg_match('/^\d{4}-\d{2}$/', $month) === 1 ? $month : date('Y-m');
        $companyId = ($actor['role'] ?? '') === 'superusuario' ? $selectedCompanyId : (int) ($actor['company_id'] ?? 0);
        if (($actor['role'] ?? '') === 'superusuario' && ($companyId ?? 0) < 1) {
            throw new InvalidArgumentException('El superusuario debe ingresar a una empresa especifica para ver su resumen operativo.');
        }

        $totals = $this->repository->monthlyTotals($normalizedMonth, $companyId);
        $income = (float) ($totals['income'] ?? 0);
        $expense = (float) ($totals['expense'] ?? 0);
        $months = array_map(
            static fn (array $row): string => (string) $row['month'],
            $this->repository->availableMonths()
        );

        if (!in_array(date('Y-m'), $months, true)) {
            array_unshift($months, date('Y-m'));
            $months = array_values(array_unique($months));
        }

        $breakdown = array_map(static function (array $row): array {
            $income = (float) ($row['income'] ?? 0);
            $expense = (float) ($row['expense'] ?? 0);

            return [
                'id' => (string) $row['id'],
                'name' => (string) $row['name'],
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }, $this->repository->monthlyBreakdown($normalizedMonth, $companyId));

        return [
            'month' => $normalizedMonth,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'months' => $months,
            'breakdown' => $breakdown,
        ];
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

    private function mapTransaction(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'companyId' => (string) $row['company_id'],
            'establishmentId' => (string) $row['establishment_id'],
            'type' => (string) $row['type'],
            'amount' => (float) $row['amount'],
            'category' => (string) $row['category'],
            'description' => (string) ($row['description'] ?? ''),
            'date' => (string) $row['transaction_date'],
            'fromTemplate' => (bool) $row['from_template'],
            'createdAt' => (string) $row['created_at'],
        ];
    }
}
