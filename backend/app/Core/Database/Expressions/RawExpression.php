<?php
declare(strict_types=1);

namespace App\Core\Database\Expressions;

use App\Core\Database\Contracts\SqlExpression;

final class RawExpression implements SqlExpression
{
    public function __construct(
        private string $sql,
        private array $params = []
    ) {
    }

    public function toSql(): string
    {
        return $this->sql;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}