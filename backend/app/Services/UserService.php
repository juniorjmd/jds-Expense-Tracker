<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstablishmentRepository;
use App\Repositories\UserRepository;
use InvalidArgumentException;

final class UserService
{
    public function __construct(
        private readonly UserRepository $repository = new UserRepository(),
        private readonly AuthService $authService = new AuthService(),
        private readonly EstablishmentRepository $establishments = new EstablishmentRepository(),
        private readonly ActivityLogService $activityLogs = new ActivityLogService()
    ) {
    }

    public function list(array $actor): array
    {
        return array_map([$this->authService, 'mapUser'], $this->repository->allVisible($actor));
    }

    public function create(array $actor, array $payload): array
    {
        $normalized = $this->normalizePayload($actor, $payload);
        $created = $this->repository->create($normalized);

        $this->activityLogs->log(
            $actor,
            'user',
            (string) $created['id'],
            'user_created',
            (int) $created['company_id'],
            null,
            'Usuario creado desde mantenimiento.',
            [
                'email' => (string) $created['email'],
                'role' => (string) $created['role'],
            ]
        );

        return $this->authService->mapUser($created);
    }

    public function update(array $actor, int $id, array $payload): array
    {
        $existing = $this->repository->find($id);
        if ($id < 1 || $existing === null) {
            throw new InvalidArgumentException('El usuario no existe.');
        }

        $this->assertAccess($actor, $existing);

        $normalized = $this->normalizePayload($actor, $payload, $id);
        $updated = $this->repository->update($id, $normalized);

        $this->activityLogs->log(
            $actor,
            'user',
            (string) $updated['id'],
            'user_updated',
            (int) $updated['company_id'],
            null,
            'Usuario actualizado desde mantenimiento.',
            [
                'email' => (string) $updated['email'],
                'role' => (string) $updated['role'],
            ]
        );

        return $this->authService->mapUser($updated);
    }

    public function delete(array $actor, int $id): bool
    {
        $existing = $this->repository->find($id);
        if ($id < 1 || $existing === null) {
            return false;
        }

        $this->assertAccess($actor, $existing);
        if ((int) $existing['id'] === (int) $actor['id']) {
            throw new InvalidArgumentException('No puedes eliminar tu propio usuario.');
        }

        if (($existing['role'] ?? '') === 'administrador'
            && $this->repository->countAdminsByCompany((int) ($existing['company_id'] ?? 0), $id) === 0
        ) {
            throw new InvalidArgumentException('Debe permanecer al menos un administrador por empresa.');
        }

        $this->activityLogs->log(
            $actor,
            'user',
            (string) $existing['id'],
            'user_deleted',
            (int) ($existing['company_id'] ?? 0),
            null,
            'Usuario eliminado desde mantenimiento.',
            [
                'email' => (string) ($existing['email'] ?? ''),
                'role' => (string) ($existing['role'] ?? ''),
            ]
        );

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

        foreach ($assigned as $establishmentId) {
            $establishmentCompanyId = $this->establishments->companyIdByEstablishment((int) $establishmentId);
            if ($establishmentCompanyId === null || $establishmentCompanyId !== $companyId) {
                throw new InvalidArgumentException('Los establecimientos asignados deben pertenecer a la misma empresa del usuario.');
            }
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
