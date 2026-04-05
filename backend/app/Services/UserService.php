<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use InvalidArgumentException;

final class UserService
{
    public function __construct(
        private readonly UserRepository $repository = new UserRepository(),
        private readonly AuthService $authService = new AuthService()
    ) {
    }

    public function list(array $actor): array
    {
        return array_map([$this->authService, 'mapUser'], $this->repository->allVisible($actor));
    }

    public function create(array $actor, array $payload): array
    {
        return $this->authService->mapUser($this->repository->create($this->normalizePayload($actor, $payload)));
    }

    public function update(array $actor, int $id, array $payload): array
    {
        $existing = $this->repository->find($id);
        if ($id < 1 || $existing === null) {
            throw new InvalidArgumentException('El usuario no existe.');
        }

        $this->assertAccess($actor, $existing);

        return $this->authService->mapUser($this->repository->update($id, $this->normalizePayload($actor, $payload, $id)));
    }

    public function delete(array $actor, int $id): bool
    {
        $existing = $this->repository->find($id);
        if ($id < 1 || $existing === null) {
            return false;
        }

        $this->assertAccess($actor, $existing);

        return $this->repository->delete($id);
    }

    private function normalizePayload(array $actor, array $payload, ?int $id = null): array
    {
        $this->assertCanManageUsers($actor);

        $name = trim((string) ($payload['name'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $role = (string) ($payload['role'] ?? 'visualizador');
        $password = (string) ($payload['password'] ?? '');
        $assigned = is_array($payload['assignedEstablishments'] ?? null) ? $payload['assignedEstablishments'] : [];
        $companyId = ($actor['role'] ?? '') === 'superusuario'
            ? (int) ($payload['companyId'] ?? 0)
            : (int) ($actor['company_id'] ?? 0);

        if ($name === '' || $email === '') {
            throw new InvalidArgumentException('Nombre y email son obligatorios.');
        }

        if (!in_array($role, ['administrador', 'editor', 'visualizador'], true)) {
            throw new InvalidArgumentException('El rol es invalido.');
        }

        if ($companyId < 1) {
            throw new InvalidArgumentException('La empresa es obligatoria.');
        }

        if ($this->repository->emailExists($email, $id)) {
            throw new InvalidArgumentException('El email ya esta registrado.');
        }

        if ($id === null && $password === '') {
            throw new InvalidArgumentException('La contrasena es obligatoria.');
        }

        return [
            'company_id' => $companyId,
            'full_name' => $name,
            'email' => $email,
            'role' => $role,
            'password_hash' => $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null,
            'assigned_establishments' => $role === 'administrador'
                ? []
                : array_values(array_filter(array_map('intval', $assigned), static fn (int $value): bool => $value > 0)),
        ];
    }

    private function assertCanManageUsers(array $actor): void
    {
        if (!in_array($actor['role'] ?? '', ['superusuario', 'administrador'], true)) {
            throw new InvalidArgumentException('No tienes permisos para administrar usuarios.');
        }
    }

    private function assertAccess(array $actor, array $subject): void
    {
        if (($actor['role'] ?? '') === 'superusuario') {
            return;
        }

        if (($subject['role'] ?? '') === 'superusuario' || (int) ($subject['company_id'] ?? 0) !== (int) ($actor['company_id'] ?? 0)) {
            throw new InvalidArgumentException('No tienes acceso a este usuario.');
        }
    }
}
