<?php
declare(strict_types=1);

namespace App\Core\Database\Clauses;

final class JoinClause
{
    public function __construct(
        private string $type,
        private string $table,
        private string $left,
        private string $operator,
        private string $right
    ) {
    }

    public static function inner(
        string $table,
        string $left,
        string $operator,
        string $right
    ): self {
        return new self('INNER', $table, $left, $operator, $right);
    }

    public static function left(
        string $table,
        string $left,
        string $operator,
        string $right
    ): self {
        return new self('LEFT', $table, $left, $operator, $right);
    }

    public function toSql(): string
    {
        return sprintf(
            '%s JOIN %s ON %s %s %s',
            $this->type,
            $this->table,
            $this->left,
            $this->operator,
            $this->right
        );
    }
}
