<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Http\Request;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use InvalidArgumentException;

final class CurrentUserService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly CompanyRepository $companies = new CompanyRepository()
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

        if (($user['role'] ?? '') === 'administrador') {
            $requestedCompanyId = (int) $request->header('x-company-id', 0);
            $assignedCompanies = $this->companies->assignedToAdmin((int) $user['id']);

            if ($requestedCompanyId > 0) {
                if (!$this->companies->isAdminAssigned($requestedCompanyId, (int) $user['id'])) {
                    throw new InvalidArgumentException('No tienes acceso a la empresa seleccionada.');
                }

                foreach ($assignedCompanies as $company) {
                    if ((int) $company['id'] === $requestedCompanyId) {
                        $user['company_id'] = $requestedCompanyId;
                        $user['company_name'] = (string) $company['name'];
                        break;
                    }
                }
            } elseif (count($assignedCompanies) === 1) {
                $user['company_id'] = (int) $assignedCompanies[0]['id'];
                $user['company_name'] = (string) $assignedCompanies[0]['name'];
            }
        }

        return $user;
    }

    public function isSuperuser(array $actor): bool
    {
        return ($actor['role'] ?? '') === 'superusuario';
    }
}
