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

    public function list(): array
    {
        return array_map([$this->authService, 'mapUser'], $this->repository->all());
    }

    public function create(array $payload): array
    {
        return $this->authService->mapUser($this->repository->create($this->normalizePayload($payload)));
    }

    public function update(int $id, array $payload): array
    {
        if ($id < 1 || $this->repository->find($id) === null) {
            throw new InvalidArgumentException('El usuario no existe.');
        }

        return $this->authService->mapUser($this->repository->update($id, $this->normalizePayload($payload, $id)));
    }

    public function delete(int $id): bool
    {
        if ($id < 1 || $this->repository->find($id) === null) {
            return false;
        }

        return $this->repository->delete($id);
    }

    private function normalizePayload(array $payload, ?int $id = null): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $role = (string) ($payload['role'] ?? 'visualizador');
        $password = (string) ($payload['password'] ?? '');
        $assigned = is_array($payload['assignedEstablishments'] ?? null) ? $payload['assignedEstablishments'] : [];

        if ($name === '' || $email === '') {
            throw new InvalidArgumentException('Nombre y email son obligatorios.');
        }

        if (!in_array($role, ['administrador', 'editor', 'visualizador'], true)) {
            throw new InvalidArgumentException('El rol es invalido.');
        }

        if ($this->repository->emailExists($email, $id)) {
            throw new InvalidArgumentException('El email ya esta registrado.');
        }

        if ($id === null && $password === '') {
            throw new InvalidArgumentException('La contrasena es obligatoria.');
        }

        return [
            'full_name' => $name,
            'email' => $email,
            'role' => $role,
            'password_hash' => $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null,
            'assigned_establishments' => $role === 'administrador'
                ? []
                : array_values(array_filter(array_map('intval', $assigned), static fn (int $value): bool => $value > 0)),
        ];
    }
}
