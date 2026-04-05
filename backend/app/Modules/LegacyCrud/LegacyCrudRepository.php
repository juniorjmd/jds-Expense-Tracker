<?php
declare(strict_types=1);

namespace App\Modules\LegacyCrud;

use App\Core\Database\BaseRepository;
use PDO;
use RuntimeException;

final class LegacyCrudRepository extends BaseRepository
{
    public function select(
        string $table,
        array $columns = ['*'],
        array $where = [],
        array $orderBy = [],
        ?int $limit = null
    ): array {
        $quotedColumns = $this->quoteColumns($columns);
        $sql = 'SELECT ' . implode(', ', $quotedColumns) . ' FROM ' . $this->quoteIdentifier($table);
        [$whereSql, $params] = $this->buildWhereClause($where);

        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }

        if ($orderBy !== []) {
            $sql .= ' ORDER BY ' . $this->buildOrderBy($orderBy);
        }

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->fetchAll($sql, $params);
    }

    public function insert(string $table, array $data): array
    {
        if ($data === []) {
            throw new RuntimeException('No se enviaron datos para insertar');
        }

        $columns = array_keys($data);
        $quotedColumns = array_map(fn (string $column) => $this->quoteIdentifier($column), $columns);
        $placeholders = array_map(fn (string $column) => ':' . $column, $columns);
        $params = [];

        foreach ($data as $column => $value) {
            $params[':' . $column] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quoteIdentifier($table),
            implode(', ', $quotedColumns),
            implode(', ', $placeholders)
        );

        $this->execute($sql, $params);

        return [
            'affected' => 1,
            'insertId' => (int) $this->db->lastInsertId(),
        ];
    }

    public function update(string $table, array $data, array $where): array
    {
        if ($data === []) {
            throw new RuntimeException('No se enviaron datos para actualizar');
        }

        [$whereSql, $params] = $this->buildWhereClause($where, 'w_');
        if ($whereSql === '') {
            throw new RuntimeException('No se permite actualizar sin condiciones');
        }

        $setParts = [];
        foreach ($data as $column => $value) {
            $param = ':s_' . $column;
            $setParts[] = $this->quoteIdentifier($column) . ' = ' . $param;
            $params[$param] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->quoteIdentifier($table),
            implode(', ', $setParts),
            $whereSql
        );

        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return [
            'affected' => $stmt->rowCount(),
        ];
    }

    public function delete(string $table, array $where): array
    {
        [$whereSql, $params] = $this->buildWhereClause($where, 'w_');
        if ($whereSql === '') {
            throw new RuntimeException('No se permite eliminar sin condiciones');
        }

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->quoteIdentifier($table),
            $whereSql
        );

        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return [
            'affected' => $stmt->rowCount(),
        ];
    }

    public function executeProcedure(string $procedure, array $params = []): array
    {
        $procedure = trim($procedure);
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $procedure)) {
            throw new RuntimeException("Procedimiento inválido: {$procedure}");
        }

        $placeholders = [];
        $bindings = [];
        $index = 0;

        foreach ($params as $value) {
            $key = ':p' . $index++;
            $placeholders[] = $key;
            $bindings[$key] = $value;
        }

        $sql = sprintf(
            'CALL %s(%s)',
            $procedure,
            implode(', ', $placeholders)
        );

        $stmt = $this->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $rows;
    }

    public function insertSelect(
        string $table,
        string $tableSelect,
        array $data,
        array $deleteBefore = [],
        array $where = []
    ): array {
        if ($data === []) {
            throw new RuntimeException('No se enviaron datos para insertar');
        }

        if ($tableSelect === '') {
            throw new RuntimeException('Debe enviar _tablaSelect');
        }

        $this->beginTransaction();

        try {
            $deleted = 0;
            if ($deleteBefore !== []) {
                [$deleteSql, $deleteParams] = $this->buildTupleWhereClause($deleteBefore, 'd_');
                $stmt = $this->prepare(sprintf(
                    'DELETE FROM %s WHERE %s',
                    $this->quoteIdentifier($table),
                    $deleteSql
                ));
                $stmt->execute($deleteParams);
                $deleted = $stmt->rowCount();
            }

            $columns = [];
            $selectParts = [];
            $params = [];
            $index = 0;

            foreach ($data as $column => $value) {
                if ($value === null) {
                    continue;
                }

                $columns[] = $this->quoteIdentifier((string) $column);

                if (is_array($value) && (($value['mode'] ?? null) === 'column')) {
                    $selectParts[] = $this->quoteIdentifier((string) ($value['value'] ?? ''));
                    continue;
                }

                $param = ':is_' . $index++;
                $selectParts[] = $param;
                $params[$param] = is_array($value) ? ($value['value'] ?? null) : $value;
            }

            if ($columns === [] || $selectParts === []) {
                throw new RuntimeException('No se enviaron columnas válidas para INSERT SELECT');
            }

            $sql = sprintf(
                'INSERT INTO %s (%s) SELECT %s FROM %s',
                $this->quoteIdentifier($table),
                implode(', ', $columns),
                implode(', ', $selectParts),
                $this->quoteIdentifier($tableSelect)
            );

            [$whereSql, $whereParams] = $this->buildWhereClause($where, 'isw_');
            if ($whereSql !== '') {
                $sql .= ' WHERE ' . $whereSql;
                $params = array_merge($params, $whereParams);
            }

            $stmt = $this->prepare($sql);
            $stmt->execute($params);

            $this->commit();

            return [
                'affected' => $stmt->rowCount(),
                'deleted' => $deleted,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->rollBack();
            }

            throw $e;
        }
    }

    public function assignUserProfile(int $profileId, int $userId): array
    {
        return $this->executeProcedure('setPerfilAUsuario', [$profileId, $userId]);
    }

    public function listBoxesByUser(int $userId): array
    {
        $sql = <<<SQL
            SELECT
                c.*,
                CASE WHEN r.id IS NULL THEN 0 ELSE 1 END AS asignada
            FROM cajas c
            LEFT JOIN rel_usuario_cajas r
                ON r.idCaja = c.id
               AND r.idUsuario = :userId
            ORDER BY c.nombre ASC
        SQL;

        return $this->fetchAll($sql, [':userId' => $userId]);
    }

    public function replaceUserBoxes(int $userId, array $boxIds): array
    {
        $this->beginTransaction();

        try {
            $delete = $this->prepare('DELETE FROM rel_usuario_cajas WHERE idUsuario = :userId');
            $delete->execute([':userId' => $userId]);

            $inserted = 0;
            if ($boxIds !== []) {
                $insert = $this->prepare(
                    'INSERT INTO rel_usuario_cajas (idUsuario, idCaja) VALUES (:userId, :boxId)'
                );

                foreach ($boxIds as $boxId) {
                    $insert->execute([
                        ':userId' => $userId,
                        ':boxId' => $boxId,
                    ]);
                    $inserted += $insert->rowCount();
                }
            }

            $this->commit();

            return [
                'deleted' => $delete->rowCount(),
                'inserted' => $inserted,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->rollBack();
            }

            throw $e;
        }
    }

    public function activeWarehouses(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre, descripcion, tipo, tipo_descripcion, obj FROM vw_inv_bodegas WHERE estado = 1 ORDER BY nombre ASC'
        );
    }

    public function establishmentsWarehouseAssignments(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre, idAuxiliar, idBodegaStock, idBodegaVitual, estockExistencia FROM establecimiento WHERE estado = 1'
        );
    }

    private function quoteColumns(array $columns): array
    {
        if ($columns === []) {
            return ['*'];
        }

        return array_map(function (string $column): string {
            if ($column === '*') {
                return '*';
            }

            return $this->quoteIdentifier($column);
        }, $columns);
    }

    private function buildOrderBy(array $orderBy): string
    {
        $parts = [];

        foreach ($orderBy as $index => $order) {
            if (!is_array($order) || count($order) < 1) {
                continue;
            }

            $column = (string) ($order[0] ?? '');
            $direction = strtoupper((string) ($order[1] ?? 'ASC'));
            if ($column === '') {
                continue;
            }

            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }

            $parts[] = $this->quoteIdentifier($column) . ' ' . $direction;
        }

        return implode(', ', $parts);
    }

    private function buildWhereClause(array $where, string $prefix = 'p_'): array
    {
        $parts = [];
        $params = [];

        foreach (array_values($where) as $index => $condition) {
            if (!is_array($condition)) {
                continue;
            }

            $column = trim((string) ($condition['columna'] ?? ''));
            $operator = strtoupper(trim((string) ($condition['tipocomp'] ?? '=')));
            $relation = strtoupper(trim((string) ($condition['relacion'] ?? 'AND')));
            $value = $condition['dato'] ?? null;

            if ($column === '') {
                continue;
            }

            $sqlRelation = $index === 0 ? '' : (($relation === 'OR') ? ' OR ' : ' AND ');
            $param = ':' . $prefix . $index;

            $comparison = match ($operator) {
                '=', '<>', '!=', '>', '>=', '<', '<=' => $this->quoteIdentifier($column) . " {$operator} {$param}",
                'LIKE' => $this->quoteIdentifier($column) . " LIKE {$param}",
                '=F' => $this->quoteIdentifier($column) . ' = ' . $this->quoteFunction((string) $value),
                'INS' => $this->quoteIdentifier($column) . ' IN ' . $this->buildSubquery($value),
                default => throw new RuntimeException("Operador no soportado: {$operator}"),
            };

            if (!in_array($operator, ['=F', 'INS'], true)) {
                $params[$param] = $operator === 'LIKE' ? '%' . $value . '%' : $value;
            }

            $parts[] = $sqlRelation . $comparison;
        }

        return [implode('', $parts), $params];
    }

    private function buildSubquery(mixed $value): string
    {
        if (!is_array($value)) {
            throw new RuntimeException('Subconsulta inválida');
        }

        $column = (string) ($value['columna'] ?? '');
        $table = (string) ($value['tabla'] ?? '');
        $checkColumn = (string) ($value['colValidacion'] ?? '');
        $checkValue = (string) ($value['datoValidacion'] ?? '');

        if ($column === '' || $table === '' || $checkColumn === '' || $checkValue === '') {
            throw new RuntimeException('Subconsulta incompleta');
        }

        return sprintf(
            '(SELECT %s FROM %s WHERE %s = %s)',
            $this->quoteIdentifier($column),
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($checkColumn),
            $checkValue
        );
    }

    private function buildTupleWhereClause(array $where, string $prefix = 't_'): array
    {
        $parts = [];
        $params = [];

        foreach (array_values($where) as $index => $condition) {
            if (!is_array($condition) || count($condition) < 3) {
                continue;
            }

            $column = trim((string) ($condition[0] ?? ''));
            $operator = strtoupper(trim((string) ($condition[1] ?? '=')));
            $value = $condition[2] ?? null;

            if ($column === '') {
                continue;
            }

            if (!in_array($operator, ['=', '<>', '!=', '>', '>=', '<', '<='], true)) {
                throw new RuntimeException("Operador no soportado: {$operator}");
            }

            $param = ':' . $prefix . $index;
            $parts[] = $this->quoteIdentifier($column) . " {$operator} {$param}";
            $params[$param] = $value;
        }

        if ($parts === []) {
            throw new RuntimeException('Condiciones inválidas para _deleteBefore');
        }

        return [implode(' AND ', $parts), $params];
    }

    private function quoteFunction(string $value): string
    {
        $value = trim($value);

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\((.*)\)$/', $value)) {
            throw new RuntimeException("Expresión de función inválida: {$value}");
        }

        return $value;
    }

    private function quoteIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            return '`' . $identifier . '`';
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            $parts = explode('.', $identifier);
            return '`' . $parts[0] . '`.`' . $parts[1] . '`';
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\([a-zA-Z_][a-zA-Z0-9_]*\)$/', $identifier)) {
            return $identifier;
        }

        throw new RuntimeException("Identificador inválido: {$identifier}");
    }
}
