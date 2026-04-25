<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database\Connection;
use App\Repositories\EstablishmentRepository;
use App\Repositories\TransactionRepository;
use InvalidArgumentException;
use Throwable;

final class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $repository = new TransactionRepository(),
        private readonly EstablishmentRepository $establishmentRepository = new EstablishmentRepository(),
        private readonly CategoryService $categories = new CategoryService(),
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

        $category = $this->categories->resolveOrCreate($actor, $companyId, $establishmentId, $type, $payload);

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
            'category_id' => (int) ($category['id'] ?? 0) ?: null,
            'type' => $type,
            'category' => (string) ($category['name'] ?? ''),
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

    public function update(array $actor, int $id, array $payload): array
    {
        $existing = $this->repository->find($id);
        if ($existing === null) {
            throw new InvalidArgumentException('La transaccion no existe.');
        }

        if (($existing['movement_group_id'] ?? null) !== null) {
            throw new InvalidArgumentException('Los movimientos internos deben editarse como grupo.');
        }

        if (($actor['role'] ?? '') !== 'superusuario' && (int) ($existing['company_id'] ?? 0) !== (int) ($actor['company_id'] ?? 0)) {
            throw new InvalidArgumentException('No tienes acceso a esta transaccion.');
        }

        $establishmentId = isset($payload['establishmentId']) ? (int) $payload['establishmentId'] : (int) $existing['establishment_id'];
        $companyId = $this->assertEstablishment($actor, $establishmentId);

        $type = strtolower(trim((string) ($payload['type'] ?? (string) ($existing['type'] ?? 'expense'))));
        if (!in_array($type, ['income', 'expense'], true)) {
            throw new InvalidArgumentException('El tipo debe ser income o expense.');
        }

        $mergedPayload = array_merge($existing, $payload);
        $category = $this->categories->resolveOrCreate($actor, $companyId, $establishmentId, $type, $mergedPayload);
        $amount = isset($payload['amount']) ? (float) $payload['amount'] : (float) ($existing['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $transactionDate = (string) ($payload['transaction_date'] ?? $existing['transaction_date'] ?? date('Y-m-d'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate) !== 1) {
            throw new InvalidArgumentException('La fecha debe tener formato YYYY-MM-DD.');
        }

        $updated = $this->repository->update($id, [
            'establishment_id' => $establishmentId,
            'category_id' => (int) ($category['id'] ?? 0) ?: null,
            'related_establishment_id' => null,
            'movement_group_id' => null,
            'type' => $type,
            'category' => (string) ($category['name'] ?? ''),
            'description' => trim((string) ($payload['description'] ?? ($existing['description'] ?? ''))),
            'amount' => round($amount, 2),
            'transaction_date' => $transactionDate,
            'from_template' => !empty($existing['from_template']),
        ]);

        $this->activityLogs->log(
            $actor,
            'transaction',
            (string) $updated['id'],
            'transaction_updated',
            (int) $updated['company_id'],
            (int) $updated['establishment_id'],
            'Movimiento actualizado.',
            [
                'type' => (string) $updated['type'],
                'amount' => (float) $updated['amount'],
                'category' => (string) $updated['category'],
            ]
        );

        return $this->mapTransaction($updated);
    }

    public function createMovement(array $actor, int $sourceEstablishmentId, array $payload): array
    {
        $sourceCompanyId = $this->assertEstablishment($actor, $sourceEstablishmentId);
        $destinationEstablishmentId = (int) ($payload['destinationEstablishmentId'] ?? $payload['destination_establishment_id'] ?? 0);
        if ($destinationEstablishmentId < 1) {
            throw new InvalidArgumentException('El establecimiento destino es obligatorio.');
        }
        if ($destinationEstablishmentId === $sourceEstablishmentId) {
            throw new InvalidArgumentException('Origen y destino deben ser diferentes.');
        }

        $destination = $this->establishmentRepository->find(
            $destinationEstablishmentId,
            date('Y-m'),
            ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0)
        );
        if ($destination === null) {
            throw new InvalidArgumentException('El establecimiento destino no existe.');
        }
        if ((int) $destination['company_id'] !== $sourceCompanyId) {
            throw new InvalidArgumentException('Origen y destino deben pertenecer a la misma empresa.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $transactionDate = (string) ($payload['transaction_date'] ?? date('Y-m-d'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate) !== 1) {
            throw new InvalidArgumentException('La fecha debe tener formato YYYY-MM-DD.');
        }

        $category = $this->categories->resolveOrCreate($actor, $sourceCompanyId, $sourceEstablishmentId, 'movement', [
            'category' => $payload['category'] ?? 'Movimiento interno',
            'category_id' => $payload['category_id'] ?? null,
            'category_scope' => $payload['category_scope'] ?? 'EMPRESA',
        ]);

        $pdo = Connection::get();
        $movementGroupId = bin2hex(random_bytes(16));

        try {
            $pdo->beginTransaction();

            $outgoing = $this->repository->create([
                'company_id' => $sourceCompanyId,
                'establishment_id' => $sourceEstablishmentId,
                'category_id' => (int) ($category['id'] ?? 0) ?: null,
                'related_establishment_id' => $destinationEstablishmentId,
                'movement_group_id' => $movementGroupId,
                'type' => 'SALIDA_POR_MOVIMIENTO',
                'category' => (string) ($category['name'] ?? 'Movimiento interno'),
                'description' => trim((string) ($payload['description'] ?? '')),
                'amount' => round($amount, 2),
                'transaction_date' => $transactionDate,
                'from_template' => false,
            ]);

            $incoming = $this->repository->create([
                'company_id' => $sourceCompanyId,
                'establishment_id' => $destinationEstablishmentId,
                'category_id' => (int) ($category['id'] ?? 0) ?: null,
                'related_establishment_id' => $sourceEstablishmentId,
                'movement_group_id' => $movementGroupId,
                'type' => 'INGRESO_POR_MOVIMIENTO',
                'category' => (string) ($category['name'] ?? 'Movimiento interno'),
                'description' => trim((string) ($payload['description'] ?? '')),
                'amount' => round($amount, 2),
                'transaction_date' => $transactionDate,
                'from_template' => false,
            ]);

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $this->activityLogs->log(
            $actor,
            'movement',
            $movementGroupId,
            'movement_created',
            $sourceCompanyId,
            $sourceEstablishmentId,
            'Movimiento interno registrado entre establecimientos.',
            [
                'sourceEstablishmentId' => $sourceEstablishmentId,
                'destinationEstablishmentId' => $destinationEstablishmentId,
                'amount' => round($amount, 2),
            ]
        );

        return $this->mapMovement($movementGroupId, [$outgoing, $incoming]);
    }

    public function updateMovement(array $actor, string $movementGroupId, array $payload): array
    {
        $movement = $this->repository->findByMovementGroup($movementGroupId, ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0));
        if (count($movement) !== 2) {
            throw new InvalidArgumentException('El movimiento no existe.');
        }

        $sourceRecord = null;
        $destinationRecord = null;
        foreach ($movement as $row) {
            if (($row['type'] ?? '') === 'SALIDA_POR_MOVIMIENTO') {
                $sourceRecord = $row;
            } elseif (($row['type'] ?? '') === 'INGRESO_POR_MOVIMIENTO') {
                $destinationRecord = $row;
            }
        }

        if ($sourceRecord === null || $destinationRecord === null) {
            throw new InvalidArgumentException('El movimiento esta inconsistente.');
        }

        $sourceEstablishmentId = isset($payload['sourceEstablishmentId'])
            ? (int) $payload['sourceEstablishmentId']
            : (int) $sourceRecord['establishment_id'];
        $destinationEstablishmentId = isset($payload['destinationEstablishmentId'])
            ? (int) $payload['destinationEstablishmentId']
            : (int) $destinationRecord['establishment_id'];

        if ($sourceEstablishmentId === $destinationEstablishmentId) {
            throw new InvalidArgumentException('Origen y destino deben ser diferentes.');
        }

        $companyId = $this->assertEstablishment($actor, $sourceEstablishmentId);
        $destination = $this->establishmentRepository->find(
            $destinationEstablishmentId,
            date('Y-m'),
            ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0)
        );
        if ($destination === null) {
            throw new InvalidArgumentException('El establecimiento destino no existe.');
        }
        if ((int) $destination['company_id'] !== $companyId) {
            throw new InvalidArgumentException('Origen y destino deben pertenecer a la misma empresa.');
        }

        $amount = isset($payload['amount']) ? (float) $payload['amount'] : (float) ($sourceRecord['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $transactionDate = (string) ($payload['transaction_date'] ?? $sourceRecord['transaction_date'] ?? date('Y-m-d'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate) !== 1) {
            throw new InvalidArgumentException('La fecha debe tener formato YYYY-MM-DD.');
        }

        $category = $this->categories->resolveOrCreate($actor, $companyId, $sourceEstablishmentId, 'movement', [
            'category' => $payload['category'] ?? $sourceRecord['category'] ?? 'Movimiento interno',
            'category_id' => $payload['category_id'] ?? $sourceRecord['category_id'] ?? null,
            'category_scope' => $payload['category_scope'] ?? 'EMPRESA',
        ]);

        $description = trim((string) ($payload['description'] ?? ($sourceRecord['description'] ?? '')));
        $pdo = Connection::get();

        try {
            $pdo->beginTransaction();

            $updatedOutgoing = $this->repository->update((int) $sourceRecord['id'], [
                'establishment_id' => $sourceEstablishmentId,
                'category_id' => (int) ($category['id'] ?? 0) ?: null,
                'related_establishment_id' => $destinationEstablishmentId,
                'movement_group_id' => $movementGroupId,
                'type' => 'SALIDA_POR_MOVIMIENTO',
                'category' => (string) ($category['name'] ?? 'Movimiento interno'),
                'description' => $description,
                'amount' => round($amount, 2),
                'transaction_date' => $transactionDate,
                'from_template' => false,
            ]);

            $updatedIncoming = $this->repository->update((int) $destinationRecord['id'], [
                'establishment_id' => $destinationEstablishmentId,
                'category_id' => (int) ($category['id'] ?? 0) ?: null,
                'related_establishment_id' => $sourceEstablishmentId,
                'movement_group_id' => $movementGroupId,
                'type' => 'INGRESO_POR_MOVIMIENTO',
                'category' => (string) ($category['name'] ?? 'Movimiento interno'),
                'description' => $description,
                'amount' => round($amount, 2),
                'transaction_date' => $transactionDate,
                'from_template' => false,
            ]);

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $this->activityLogs->log(
            $actor,
            'movement',
            $movementGroupId,
            'movement_updated',
            $companyId,
            $sourceEstablishmentId,
            'Movimiento interno actualizado.',
            [
                'sourceEstablishmentId' => $sourceEstablishmentId,
                'destinationEstablishmentId' => $destinationEstablishmentId,
                'amount' => round($amount, 2),
            ]
        );

        return $this->mapMovement($movementGroupId, [$updatedOutgoing, $updatedIncoming]);
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
            ($transaction['movement_group_id'] ?? null) !== null ? 'movement' : 'transaction',
            (string) (($transaction['movement_group_id'] ?? null) !== null ? $transaction['movement_group_id'] : $transaction['id']),
            ($transaction['movement_group_id'] ?? null) !== null ? 'movement_deleted' : 'transaction_deleted',
            (int) ($transaction['company_id'] ?? 0),
            (int) ($transaction['establishment_id'] ?? 0),
            ($transaction['movement_group_id'] ?? null) !== null ? 'Movimiento interno eliminado.' : 'Movimiento eliminado.',
            [
                'type' => (string) ($transaction['type'] ?? ''),
                'amount' => (float) ($transaction['amount'] ?? 0),
                'category' => (string) ($transaction['category'] ?? ''),
            ]
        );

        if (($transaction['movement_group_id'] ?? null) !== null) {
            return $this->repository->deleteByMovementGroup((string) $transaction['movement_group_id'], ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0));
        }

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
            'categoryId' => isset($row['category_id']) ? (string) $row['category_id'] : null,
            'relatedEstablishmentId' => isset($row['related_establishment_id']) ? (string) $row['related_establishment_id'] : null,
            'movementGroupId' => (string) ($row['movement_group_id'] ?? ''),
            'type' => (string) $row['type'],
            'amount' => (float) $row['amount'],
            'category' => (string) $row['category'],
            'description' => (string) ($row['description'] ?? ''),
            'date' => (string) $row['transaction_date'],
            'fromTemplate' => (bool) $row['from_template'],
            'createdAt' => (string) $row['created_at'],
            'updatedAt' => (string) ($row['updated_at'] ?? $row['created_at']),
        ];
    }

    private function mapMovement(string $movementGroupId, array $rows): array
    {
        $mapped = array_map([$this, 'mapTransaction'], $rows);
        $source = null;
        $destination = null;
        foreach ($mapped as $item) {
            if (($item['type'] ?? '') === 'SALIDA_POR_MOVIMIENTO') {
                $source = $item;
            } elseif (($item['type'] ?? '') === 'INGRESO_POR_MOVIMIENTO') {
                $destination = $item;
            }
        }

        return [
            'movementGroupId' => $movementGroupId,
            'source' => $source,
            'destination' => $destination,
            'transactions' => $mapped,
        ];
    }
}
