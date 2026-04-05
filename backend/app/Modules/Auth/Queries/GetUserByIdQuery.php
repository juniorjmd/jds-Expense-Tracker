<?php
declare(strict_types=1);

namespace App\Modules\Auth\Queries;

use App\Core\Database\QueryBuilder;

final class GetUserByIdQuery
{
    public function __construct(
        private int $id
    ) {
    }

    public function build(): QueryBuilder
    {
        return (new QueryBuilder())
            ->table('vw_usuario_response_ok')
            ->select(['*'])
            ->where('ID', '=', $this->id);
    }
}
