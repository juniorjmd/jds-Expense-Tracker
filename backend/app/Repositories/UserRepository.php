<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;

final class UserRepository extends BaseRepository
{
    public function allVisible(array $actor): array
    {
        $sql = 'SELECT
                u.id,
                u.company_id,
                c.name AS company_name,
                u.full_name,
                u.email,
                u.role,
                u.created_at,
                GROUP_CONCAT(ue.establishment_id ORDER BY ue.establishment_id SEPARATOR ",") AS assigned_establishments
             FROM users u
             LEFT JOIN companies c ON c.id = u.company_id
             LEFT JOIN user_establishments ue ON ue.user_id = u.id';

        $params = [];
        if (($actor['role'] ?? '') !== 'superusuario') {
            $sql .= ' WHERE u.role <> "superusuario"
                      AND (
                        u.company_id = :company_id_user
                        OR EXISTS (
                            SELECT 1 FROM company_admin_users cau
                            WHERE cau.user_id = u.id AND cau.company_id = :company_id_admin
                        )
                      )';
            $params[':company_id_user'] = (int) ($actor['company_id'] ?? 0);
            $params[':company_id_admin'] = (int) ($actor['company_id'] ?? 0);
        }

        $sql .= ' GROUP BY u.id, u.company_id, c.name, u.full_name, u.email, u.role, u.created_at
                  ORDER BY u.created_at DESC, u.id DESC';

        return $this->fetchAll($sql, $params);
    }

    public function allByCompany(int $companyId): array
    {
        return $this->fetchAll(
            'SELECT
                u.id,
                u.company_id,
                c.name AS company_name,
                u.full_name,
                u.email,
                u.role,
                u.created_at,
                GROUP_CONCAT(ue.establishment_id ORDER BY ue.establishment_id SEPARATOR ",") AS assigned_establishments
             FROM users u
             LEFT JOIN companies c ON c.id = u.company_id
             LEFT JOIN user_establishments ue ON ue.user_id = u.id
             WHERE u.role <> "superusuario"
               AND (
                    u.company_id = :company_id_user
                    OR EXISTS (
                        SELECT 1 FROM company_admin_users cau
                        WHERE cau.user_id = u.id AND cau.company_id = :company_id_admin
                    )
               )
             GROUP BY u.id, u.company_id, c.name, u.full_name, u.email, u.role, u.created_at
             ORDER BY u.created_at DESC, u.id DESC',
            [
                ':company_id_user' => $companyId,
                ':company_id_admin' => $companyId,
            ]
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT
                u.id,
                u.company_id,
                c.name AS company_name,
                u.full_name,
                u.email,
                u.role,
                u.created_at,
                GROUP_CONCAT(ue.establishment_id ORDER BY ue.establishment_id SEPARATOR ",") AS assigned_establishments
             FROM users u
             LEFT JOIN companies c ON c.id = u.company_id
             LEFT JOIN user_establishments ue ON ue.user_id = u.id
             WHERE u.id = :id
             GROUP BY u.id, u.company_id, c.name, u.full_name, u.email, u.role, u.created_at',
            [':id' => $id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT
                u.id,
                u.company_id,
                c.name AS company_name,
                u.full_name,
                u.email,
                u.password_hash,
                u.role,
                u.created_at,
                GROUP_CONCAT(ue.establishment_id ORDER BY ue.establishment_id SEPARATOR ",") AS assigned_establishments
             FROM users u
             LEFT JOIN companies c ON c.id = u.company_id
             LEFT JOIN user_establishments ue ON ue.user_id = u.id
             WHERE u.email = :email
             GROUP BY u.id, u.company_id, c.name, u.full_name, u.email, u.password_hash, u.role, u.created_at',
            [':email' => $email]
        );
    }

    public function findWithPasswordHash(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, company_id, full_name, email, password_hash, role, created_at
             FROM users
             WHERE id = :id
             LIMIT 1',
            [':id' => $id]
        );
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = [':email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        return $this->fetchOne($sql, $params) !== null;
    }

    public function create(array $payload): array
    {
        $this->execute(
            'INSERT INTO users (company_id, full_name, email, password_hash, role)
             VALUES (:company_id, :full_name, :email, :password_hash, :role)',
            [
                ':company_id' => $payload['company_id'],
                ':full_name' => $payload['full_name'],
                ':email' => $payload['email'],
                ':password_hash' => $payload['password_hash'],
                ':role' => $payload['role'],
            ]
        );

        $id = (int) $this->db->lastInsertId();
        $this->syncAssignments($id, $payload['assigned_establishments'] ?? []);

        return $this->find($id) ?? [];
    }

    public function update(int $id, array $payload): array
    {
        $fields = [
            'company_id = :company_id',
            'full_name = :full_name',
            'email = :email',
            'role = :role',
        ];

        $params = [
            ':id' => $id,
            ':company_id' => $payload['company_id'],
            ':full_name' => $payload['full_name'],
            ':email' => $payload['email'],
            ':role' => $payload['role'],
        ];

        if (!empty($payload['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $params[':password_hash'] = $payload['password_hash'];
        }

        $this->execute(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id',
            $params
        );

        $this->syncAssignments($id, $payload['assigned_establishments'] ?? []);

        return $this->find($id) ?? [];
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM users WHERE id = :id', [':id' => $id]);
    }

    public function countAdminsByCompany(int $companyId, ?int $excludeId = null): int
    {
        $sql = 'SELECT COUNT(DISTINCT u.id) AS total
                FROM users u
                LEFT JOIN company_admin_users cau ON cau.user_id = u.id
                WHERE role = "administrador"
                  AND (u.company_id = :company_id_user OR cau.company_id = :company_id_admin)';
        $params = [
            ':company_id_user' => $companyId,
            ':company_id_admin' => $companyId,
        ];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $row = $this->fetchOne($sql, $params);

        return (int) ($row['total'] ?? 0);
    }

    public function setPrimaryCompany(int $userId, int $companyId): void
    {
        $this->execute(
            'UPDATE users SET company_id = :company_id WHERE id = :id AND (company_id IS NULL OR company_id = 0)',
            [
                ':company_id' => $companyId,
                ':id' => $userId,
            ]
        );
    }

    private function syncAssignments(int $userId, array $establishmentIds): void
    {
        $this->execute('DELETE FROM user_establishments WHERE user_id = :user_id', [':user_id' => $userId]);

        foreach ($establishmentIds as $establishmentId) {
            $this->execute(
                'INSERT INTO user_establishments (user_id, establishment_id)
                 VALUES (:user_id, :establishment_id)',
                [
                    ':user_id' => $userId,
                    ':establishment_id' => (int) $establishmentId,
                ]
            );
        }
    }
}
