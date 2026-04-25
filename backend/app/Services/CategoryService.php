<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstablishmentRepository;
use App\Repositories\CategoryRepository;
use InvalidArgumentException;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository = new CategoryRepository(),
        private readonly EstablishmentRepository $establishments = new EstablishmentRepository()
    ) {
    }

    public function list(array $actor, ?string $type = null, ?int $establishmentId = null): array
    {
        $normalizedType = $type !== null ? strtolower(trim($type)) : null;
        if (!in_array($normalizedType, [null, '', 'income', 'expense', 'movement'], true)) {
            $normalizedType = null;
        }

        $companyId = $this->resolveCompanyId($actor, $establishmentId);

        return array_map([$this, 'mapCategory'], $this->repository->allByCompany($companyId, $normalizedType ?: null, $establishmentId));
    }

    public function create(array $actor, array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('El nombre de la categoria es obligatorio.');
        }

        $type = strtolower(trim((string) ($payload['type'] ?? 'expense')));
        if (!in_array($type, ['income', 'expense', 'movement'], true)) {
            throw new InvalidArgumentException('El tipo de categoria no es valido.');
        }

        $scope = strtoupper(trim((string) ($payload['scope'] ?? 'ESTABLECIMIENTO')));
        if (!in_array($scope, ['EMPRESA', 'ESTABLECIMIENTO'], true)) {
            throw new InvalidArgumentException('El alcance de la categoria no es valido.');
        }

        $establishmentId = isset($payload['establishmentId']) ? (int) $payload['establishmentId'] : null;
        $companyId = $this->resolveCompanyId($actor, $establishmentId);

        if ($scope === 'ESTABLECIMIENTO' && ($establishmentId ?? 0) < 1) {
            throw new InvalidArgumentException('La categoria de establecimiento requiere un establecimiento asociado.');
        }

        if ($scope === 'EMPRESA' && !in_array((string) ($actor['role'] ?? ''), ['superusuario', 'administrador'], true)) {
            throw new InvalidArgumentException('No tienes permisos para crear categorias generales.');
        }

        $existing = $this->repository->findScopedByName(
            $companyId,
            $name,
            $type,
            $scope,
            $scope === 'ESTABLECIMIENTO' ? $establishmentId : null
        );

        if ($existing !== null) {
            throw new InvalidArgumentException('La categoria ya existe para ese alcance.');
        }

        return $this->mapCategory($this->repository->create([
            'company_id' => $companyId,
            'establishment_id' => $scope === 'ESTABLECIMIENTO' ? $establishmentId : null,
            'name' => $name,
            'type' => $type,
            'scope' => $scope,
            'color' => trim((string) ($payload['color'] ?? '')) ?: null,
        ]));
    }

    public function resolveOrCreate(array $actor, int $companyId, int $establishmentId, string $type, array $payload): array
    {
        $categoryName = trim((string) ($payload['category'] ?? ''));
        if ($categoryName === '') {
            throw new InvalidArgumentException('La categoria es obligatoria.');
        }

        $normalizedType = $type === 'movement' ? 'movement' : $type;

        $selectedCategoryId = (int) ($payload['category_id'] ?? 0);
        if ($selectedCategoryId > 0) {
            $category = $this->repository->find($selectedCategoryId);
            if ($category === null || (int) ($category['company_id'] ?? 0) !== $companyId) {
                throw new InvalidArgumentException('La categoria seleccionada no existe.');
            }

            if (
                (string) ($category['scope'] ?? '') === 'ESTABLECIMIENTO'
                && (int) ($category['establishment_id'] ?? 0) !== $establishmentId
            ) {
                throw new InvalidArgumentException('La categoria seleccionada no pertenece al establecimiento.');
            }

            return $category;
        }

        $scope = strtoupper(trim((string) ($payload['category_scope'] ?? 'ESTABLECIMIENTO')));
        if (!in_array($scope, ['EMPRESA', 'ESTABLECIMIENTO'], true)) {
            $scope = 'ESTABLECIMIENTO';
        }

        $existing = $this->repository->findScopedByName(
            $companyId,
            $categoryName,
            $normalizedType,
            $scope,
            $scope === 'ESTABLECIMIENTO' ? $establishmentId : null
        );

        if ($existing !== null) {
            return $existing;
        }

        return $this->repository->create([
            'company_id' => $companyId,
            'establishment_id' => $scope === 'ESTABLECIMIENTO' ? $establishmentId : null,
            'name' => $categoryName,
            'type' => $normalizedType,
            'scope' => $scope,
            'color' => null,
        ]);
    }

    private function resolveCompanyId(array $actor, ?int $establishmentId): int
    {
        if ($establishmentId !== null && $establishmentId > 0) {
            $establishment = $this->establishments->find(
                $establishmentId,
                date('Y-m'),
                ($actor['role'] ?? '') === 'superusuario' ? null : (int) ($actor['company_id'] ?? 0)
            );
            if ($establishment === null) {
                throw new InvalidArgumentException('El establecimiento no existe.');
            }

            return (int) $establishment['company_id'];
        }

        $companyId = (int) ($actor['company_id'] ?? 0);
        if (($actor['role'] ?? '') === 'superusuario' && $companyId < 1) {
            throw new InvalidArgumentException('Debes seleccionar una empresa para gestionar categorias.');
        }
        if ($companyId < 1) {
            throw new InvalidArgumentException('No se encontro la empresa asociada.');
        }

        return $companyId;
    }

    private function mapCategory(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'companyId' => (string) $row['company_id'],
            'establishmentId' => isset($row['establishment_id']) ? (string) $row['establishment_id'] : null,
            'name' => (string) $row['name'],
            'type' => (string) $row['type'],
            'scope' => (string) $row['scope'],
            'color' => (string) ($row['color'] ?? ''),
            'createdAt' => (string) $row['created_at'],
            'updatedAt' => (string) ($row['updated_at'] ?? $row['created_at']),
        ];
    }
}
