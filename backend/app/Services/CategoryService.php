<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository = new CategoryRepository()
    ) {
    }

    public function list(?string $type = null): array
    {
        $normalizedType = $type !== null ? strtolower(trim($type)) : null;
        if (!in_array($normalizedType, [null, '', 'income', 'expense'], true)) {
            $normalizedType = null;
        }

        return $this->repository->all($normalizedType ?: null);
    }
}
