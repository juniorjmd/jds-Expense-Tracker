<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Http\Request;
use App\Repositories\UserRepository;
use InvalidArgumentException;

final class CurrentUserService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository()
    ) {
    }

    public function require(Request $request): array
    {
        $userId = (int) $request->header('x-user-id', 0);
        if ($userId < 1) {
            throw new InvalidArgumentException('No se encontro el usuario autenticado.');
        }

        $user = $this->users->find($userId);
        if ($user === null) {
            throw new InvalidArgumentException('El usuario autenticado no existe.');
        }

        return $user;
    }

    public function isSuperuser(array $actor): bool
    {
        return ($actor['role'] ?? '') === 'superusuario';
    }
}
