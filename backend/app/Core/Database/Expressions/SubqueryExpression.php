<?php
declare(strict_types=1);

namespace App\Core\Database\Expressions;

use App\Core\Database\Contracts\SqlExpression;
use App\Core\Database\QueryBuilder;

final class SubqueryExpression implements SqlExpression
{
    public function __construct(
        private QueryBuilder $queryBuilder
    ) {
    }

    public function toSql(): string
    {
        return '(' . $this->queryBuilder->toSql() . ')';
    }

    public function getParams(): array
    {
        return $this->queryBuilder->getParams();
    }
}