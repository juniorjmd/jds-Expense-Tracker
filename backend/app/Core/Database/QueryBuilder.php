<?php
declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Database\Contracts\SqlExpression;
use App\Core\Database\Clauses\JoinClause;
use InvalidArgumentException;

final class QueryBuilder
{
    private string $table = '';
    private array $columns = ['*'];
    private array $wheres = [];
    private array $params = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $joins = [];

    public function join(JoinClause $join): self
{
    $this->joins[] = $join;
    return $this;
}
    public function table(string $table): self
    {
        $this->assertIdentifier($table, 'tabla');
        $this->table = $this->wrapIdentifier($table);
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        if ($columns === []) {
            throw new InvalidArgumentException('Debe indicar al menos una columna.');
        }

        foreach ($columns as $column) {
            if ($column !== '*') {
                $this->assertIdentifier($column, 'columna');
            }
        }

        $this->columns = array_map(function (string $column) {
           return $column === '*' ? '*' : $this->wrapIdentifier($column);
            }, $columns);
        return $this;
    }

    public function selectExpression(SqlExpression $expression, ?string $alias = null): self
    {
        $sql = $expression->toSql();

        if ($alias !== null) {
            $this->assertIdentifier($alias, 'alias');
            $sql .= " AS {$alias}";
        }

        if ($this->columns === ['*']) {
            $this->columns = [];
        }

        $this->columns[] = $sql;
        $this->params = array_merge($this->params, $expression->getParams());

        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->assertIdentifier($column, 'columna');

        $allowedOperators = ['=', '<>', '!=', '<', '>', '<=', '>=', 'LIKE'];
        $operator = strtoupper(trim($operator));

        if (!in_array($operator, $allowedOperators, true)) {
            throw new InvalidArgumentException("Operador no permitido: {$operator}");
        }

        $paramName = ':w_' . count($this->params);

        $this->wheres[] = $this->wrapIdentifier($column) . " {$operator} {$paramName}";
        $this->params[$paramName] = $value;

        return $this;
    }

    public function whereExpression(SqlExpression $expression): self
    {
        $this->wheres[] = $expression->toSql();
        $this->params = array_merge($this->params, $expression->getParams());

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $this->assertIdentifier($column, 'columna');

        if ($values === []) {
            throw new InvalidArgumentException('whereIn requiere al menos un valor.');
        }

        $placeholders = [];

        foreach ($values as $value) {
            $paramName = ':w_' . count($this->params);
            $this->params[$paramName] = $value;
            $placeholders[] = $paramName;
        }

        $in = implode(', ', $placeholders);

        $this->wheres[] =  $this->wrapIdentifier($column) . " IN ({$in})";

        return $this;
    }

    public function whereInSubquery(string $column, SqlExpression $subquery): self
    {
        $this->assertIdentifier($column, 'columna');

        $this->wheres[] = $this->wrapIdentifier($column) . " IN " . $subquery->toSql();
        $this->params = array_merge($this->params, $subquery->getParams());

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->assertIdentifier($column, 'columna');

        $direction = strtoupper(trim($direction));
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException('La dirección de orderBy debe ser ASC o DESC.');
        }

        $this->orderBy[] = $this->wrapIdentifier($column) . " {$direction}";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('El limit debe ser mayor que 0.');
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('El offset no puede ser negativo.');
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function toSql(): string
    {
        if ($this->table === '') {
            throw new InvalidArgumentException('Debe indicar la tabla.');
        }

        $columns = implode(', ', $this->columns);
        $sql = "SELECT {$columns} FROM {$this->table}";

        if ($this->joins !== []) {
            foreach ($this->joins as $join) {
                $sql .= ' ' . $join->toSql();
            }
        }
        if ($this->wheres !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if ($this->orderBy !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->offset}, {$this->limit}";
        }

        return $sql;
    }

    private function wrapIdentifier(string $value): string
{
    if (str_contains($value, '.')) {
        $parts = explode('.', $value);

        $parts = array_map(function (string $part) {
            return '`' . $part . '`';
        }, $parts);

        return implode('.', $parts);
    }

    return '`' . $value . '`';
}
    public function getParams(): array
    {
        return $this->params;
    }

    public function reset(): self
    {
        $this->table = '';
        $this->columns = ['*'];
        $this->wheres = [];
        $this->params = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->joins = [];

        return $this;
    }

    private function assertIdentifier(string $value, string $label): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $value)) {
            throw new InvalidArgumentException("{$label} inválido: {$value}");
        }
    }
}