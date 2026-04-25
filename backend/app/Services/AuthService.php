<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use InvalidArgumentException;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $repository = new UserRepository(),
        private readonly CompanyRepository $companies = new CompanyRepository()
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

    public function changeSuperuserPassword(array $payload): array
    {
        $currentPassword = (string) ($payload['currentPassword'] ?? '');
        $newPassword = (string) ($payload['newPassword'] ?? '');
        $confirmPassword = (string) ($payload['confirmPassword'] ?? '');
        $superuserEmail = strtolower(trim((string) ($_ENV['SUPERUSER_EMAIL'] ?? 'sp-et@sofdla.net')));

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            throw new InvalidArgumentException('Debes completar la contrasena actual, la nueva y su confirmacion.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException('La nueva contrasena y la confirmacion no coinciden.');
        }

        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('La nueva contrasena debe tener al menos 8 caracteres.');
        }

        if ($currentPassword === $newPassword) {
            throw new InvalidArgumentException('La nueva contrasena debe ser diferente a la actual.');
        }

        $superuser = $this->repository->findByEmail($superuserEmail);
        if ($superuser === null || (string) ($superuser['role'] ?? '') !== 'superusuario') {
            throw new InvalidArgumentException('No se encontro el superusuario configurado.');
        }

        if (!password_verify($currentPassword, (string) ($superuser['password_hash'] ?? ''))) {
            throw new InvalidArgumentException('La contrasena actual no es correcta.');
        }

        $updated = $this->repository->update((int) $superuser['id'], [
            'company_id' => $superuser['company_id'] ?? null,
            'full_name' => (string) ($superuser['full_name'] ?? ''),
            'email' => (string) ($superuser['email'] ?? ''),
            'role' => 'superusuario',
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
            'assigned_establishments' => [],
        ]);

        return [
            'updated' => true,
            'email' => (string) ($updated['email'] ?? $superuserEmail),
            'role' => (string) ($updated['role'] ?? 'superusuario'),
        ];
    }

    public function resetSuperuserPassword(array $payload): array
    {
        $masterKey = (string) ($payload['masterKey'] ?? '');
        $newPassword = (string) ($payload['newPassword'] ?? '');
        $confirmPassword = (string) ($payload['confirmPassword'] ?? '');
        $expectedMasterKey = (string) ($_ENV['SUPERUSER_RESET_KEY'] ?? '');
        $superuserEmail = strtolower(trim((string) ($_ENV['SUPERUSER_EMAIL'] ?? 'sp-et@sofdla.net')));

        if ($masterKey === '' || $newPassword === '' || $confirmPassword === '') {
            throw new InvalidArgumentException('Debes completar la llave maestra, la nueva contrasena y su confirmacion.');
        }

        if ($expectedMasterKey === '') {
            throw new InvalidArgumentException('La llave maestra del superusuario no esta configurada.');
        }

        if (!hash_equals($expectedMasterKey, $masterKey)) {
            throw new InvalidArgumentException('La llave maestra no es valida.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException('La nueva contrasena y la confirmacion no coinciden.');
        }

        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('La nueva contrasena debe tener al menos 8 caracteres.');
        }

        $superuser = $this->repository->findByEmail($superuserEmail);
        if ($superuser === null || (string) ($superuser['role'] ?? '') !== 'superusuario') {
            throw new InvalidArgumentException('No se encontro el superusuario configurado.');
        }

        $updated = $this->repository->update((int) $superuser['id'], [
            'company_id' => $superuser['company_id'] ?? null,
            'full_name' => (string) ($superuser['full_name'] ?? ''),
            'email' => (string) ($superuser['email'] ?? ''),
            'role' => 'superusuario',
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
            'assigned_establishments' => [],
        ]);

        return [
            'updated' => true,
            'email' => (string) ($updated['email'] ?? $superuserEmail),
            'role' => (string) ($updated['role'] ?? 'superusuario'),
            'mode' => 'master_key',
        ];
    }

    public function mapUser(array $row): array
    {
        $assignments = trim((string) ($row['assigned_establishments'] ?? ''));
        $administeredCompanies = ($row['role'] ?? '') === 'administrador'
            ? array_map(static fn (array $company): array => [
                'id' => (string) $company['id'],
                'name' => (string) $company['name'],
                'description' => (string) ($company['description'] ?? ''),
                'planName' => (string) ($company['plan_name'] ?? ''),
                'subscriptionStatus' => (string) ($company['subscription_status'] ?? ''),
            ], $this->companies->assignedToAdmin((int) $row['id']))
            : [];
        $defaultCompany = $administeredCompanies[0] ?? null;

        return [
            'id' => (string) $row['id'],
            'companyId' => $defaultCompany['id'] ?? (isset($row['company_id']) && $row['company_id'] !== null ? (string) $row['company_id'] : null),
            'companyName' => $defaultCompany['name'] ?? (isset($row['company_name']) ? (string) $row['company_name'] : null),
            'name' => (string) $row['full_name'],
            'email' => (string) $row['email'],
            'role' => (string) $row['role'],
            'assignedEstablishments' => $assignments === '' ? [] : array_map('strval', explode(',', $assignments)),
            'administeredCompanies' => $administeredCompanies,
            'createdAt' => (string) $row['created_at'],
        ];
    }
}
