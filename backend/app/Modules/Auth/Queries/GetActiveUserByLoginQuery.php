<?php
declare(strict_types=1);

namespace App\Modules\Auth\Queries;

use App\Core\Database\QueryBuilder;

final class GetActiveUserByLoginQuery
{
    public function __construct(
        private string $login
    ) {
    }

    public function build(): QueryBuilder
    {
        return (new QueryBuilder())
            ->table('vw_usuario')
            ->select(['*'])
            ->where('Login', '=', $this->login)
            ->where('estado', '=', 1);
    }
}
