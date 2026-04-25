<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;
use App\Core\Database\QueryBuilder;

final class CategoryRepository extends BaseRepository
{
    public function allByCompany(int $companyId, ?string $type = null, ?int $establishmentId = null): array
    {
        if ($establishmentId !== null && $establishmentId > 0) {
            $sql = 'SELECT id, company_id, establishment_id, name, type, scope, color, created_at, updated_at
                    FROM categories
                    WHERE company_id = :company_id';
            $params = [':company_id' => $companyId];

            if ($type !== null && $type !== '') {
                $sql .= ' AND type = :type';
                $params[':type'] = $type;
            }

            $sql .= ' AND (scope = :scope_company OR establishment_id = :establishment_id)
                      ORDER BY name ASC';
            $params[':scope_company'] = 'EMPRESA';
            $params[':establishment_id'] = $establishmentId;

            return $this->fetchAll($sql, $params);
        }

        $builder = (new QueryBuilder())
            ->table('categories')
            ->select(['id', 'company_id', 'establishment_id', 'name', 'type', 'scope', 'color', 'created_at', 'updated_at'])
            ->where('company_id', '=', $companyId)
            ->orderBy('name');

        if ($type !== null && $type !== '') {
            $builder->where('type', '=', $type);
        }

        return $this->fetchAll($builder->toSql(), $builder->getParams());
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, company_id, establishment_id, name, type, scope, color, created_at, updated_at
             FROM categories
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function findScopedByName(
        int $companyId,
        string $name,
        string $type,
        string $scope,
        ?int $establishmentId = null
    ): ?array {
        if ($scope === 'EMPRESA') {
            return $this->fetchOne(
                'SELECT id, company_id, establishment_id, name, type, scope, color, created_at, updated_at
                 FROM categories
                 WHERE company_id = :company_id
                   AND scope = :scope
                   AND type = :type
                   AND LOWER(name) = LOWER(:name)
                 ORDER BY id DESC
                 LIMIT 1',
                [
                    ':company_id' => $companyId,
                    ':scope' => $scope,
                    ':type' => $type,
                    ':name' => $name,
                ]
            );
        }

        return $this->fetchOne(
            'SELECT id, company_id, establishment_id, name, type, scope, color, created_at, updated_at
             FROM categories
             WHERE company_id = :company_id
               AND establishment_id = :establishment_id
               AND scope = :scope
               AND type = :type
               AND LOWER(name) = LOWER(:name)
             ORDER BY id DESC
             LIMIT 1',
            [
                ':company_id' => $companyId,
                ':establishment_id' => $establishmentId,
                ':scope' => $scope,
                ':type' => $type,
                ':name' => $name,
            ]
        );
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO categories (company_id, establishment_id, name, type, scope, color)
             VALUES (:company_id, :establishment_id, :name, :type, :scope, :color)',
            [
                ':company_id' => $payload['company_id'],
                ':establishment_id' => $payload['establishment_id'] ?? null,
                ':name' => $payload['name'],
                ':type' => $payload['type'],
                ':scope' => $payload['scope'],
                ':color' => $payload['color'] ?? null,
            ]
        );

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }
}
