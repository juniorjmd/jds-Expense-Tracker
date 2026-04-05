<?php

namespace App\Core\Database\Contracts;

interface SqlExpression
{
 public function toSql(): string;

    public function getParams(): array;
}
