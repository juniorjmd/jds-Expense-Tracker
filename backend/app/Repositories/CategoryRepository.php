<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\BaseRepository;
use App\Core\Database\QueryBuilder;

final class CategoryRepository extends BaseRepository
{
    public function all(?string $type = null): array
    {
        $builder = (new QueryBuilder())
            ->table('categories')
            ->select(['id', 'name', 'type', 'color', 'created_at'])
            ->orderBy('name');

        if ($type !== null && $type !== '') {
            $builder->where('type', '=', $type);
        }

        return $this->fetchAll($builder->toSql(), $builder->getParams());
    }
}
