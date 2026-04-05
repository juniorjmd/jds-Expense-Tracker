<?php

namespace App\Modules\Auth\Queries;

use App\Core\Database\QueryBuilder;

class GetUserByTokenQuery
{
    public function __construct(
        private string $token
    ) {
    }

    public function build(): QueryBuilder
    {
        return (new QueryBuilder())
            ->table('vw_session')
            ->select(['*'])
            ->where('key', '=', $this->token);
    }
}
