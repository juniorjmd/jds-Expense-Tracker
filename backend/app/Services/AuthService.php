<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use InvalidArgumentException;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $repository = new UserRepository()
    ) {
    }

    public function login(array $payload): array
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException('Email y contrasena son obligatorios.');
        }

        $user = $this->repository->findByEmail($email);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            throw new InvalidArgumentException('Credenciales invalidas.');
        }

        return $this->mapUser($user);
    }

    public function mapUser(array $row): array
    {
        $assignments = trim((string) ($row['assigned_establishments'] ?? ''));

        return [
            'id' => (string) $row['id'],
            'name' => (string) $row['full_name'],
            'email' => (string) $row['email'],
            'role' => (string) $row['role'],
            'assignedEstablishments' => $assignments === '' ? [] : array_map('strval', explode(',', $assignments)),
            'createdAt' => (string) $row['created_at'],
        ];
    }
}
