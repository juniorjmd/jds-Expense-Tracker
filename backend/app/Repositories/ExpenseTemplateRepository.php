<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class ExpenseTemplateRepository extends BaseRepository
{
    public function byEstablishment(int $establishmentId): array
    {
        return $this->fetchAll(
            'SELECT id, establishment_id, category, description, amount, created_at
             FROM expense_templates
             WHERE establishment_id = :establishment_id
             ORDER BY created_at DESC, id DESC',
            [':establishment_id' => $establishmentId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, establishment_id, category, description, amount, created_at
             FROM expense_templates
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO expense_templates (establishment_id, category, description, amount)
             VALUES (:establishment_id, :category, :description, :amount)',
            [
                ':establishment_id' => $payload['establishment_id'],
                ':category' => $payload['category'],
                ':description' => $payload['description'] ?? null,
                ':amount' => $payload['amount'],
            ]
        );

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM expense_templates WHERE id = :id', [':id' => $id]);
    }
}
