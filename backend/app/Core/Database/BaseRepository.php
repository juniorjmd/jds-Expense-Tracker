<?php
declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOStatement;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Connection::get();
    }

    protected function prepare(string $sql): PDOStatement
    {
        return $this->db->prepare($sql);
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    protected function callProcedure(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        return $result;
    }

    protected function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    protected function commit(): bool
    {
        return $this->db->commit();
    }

    protected function rollBack(): bool
    {
        return $this->db->rollBack();
    }
}