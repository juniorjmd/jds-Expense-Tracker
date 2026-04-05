<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class ExpenseTemplateRepository extends BaseRepository
{
    public function byEstablishment(int $establishmentId, ?int $companyId = null): array
    {
        $sql = 'SELECT id, company_id, establishment_id, category, description, amount, created_at
             FROM expense_templates
             WHERE establishment_id = :establishment_id';
        $params = [':establishment_id' => $establishmentId];

        if ($companyId !== null) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = $companyId;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';

        return $this->fetchAll(
            $sql,
            $params
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, company_id, establishment_id, category, description, amount, created_at
             FROM expense_templates
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO expense_templates (company_id, establishment_id, category, description, amount)
             VALUES (:company_id, :establishment_id, :category, :description, :amount)',
            [
                ':company_id' => $payload['company_id'],
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
