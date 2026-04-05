<?php
declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Auth\Services\AuthService;

final class AuthController
{
    public function __construct(
        private ?AuthService $service = null
    ) {
        $this->service ??= new AuthService();
    }

    public function index(Request $request): array
    {
        return [
            'module' => 'auth',
            'message' => 'Auth module activo'
        ];
    }

    public function login(Request $request): array
    {
        $result = $this->service->login($request);

        $this->failIfNeeded($result, 'Error de autenticación');

        return $result['data'] ?? [];
    }

    public function validatekey(Request $request): array
    {
        $result = $this->service->validateKey($request);

        $this->failIfNeeded($result, 'Error validando llave');

        return $result['data'] ?? [];
    }

    public function me(Request $request): array
    {
        $result = $this->service->me($request);

        $this->failIfNeeded($result, 'Error obteniendo usuario autenticado');

        return $result['data'] ?? [];
    }

    public function resetpassword(Request $request): array
    {
        $result = $this->service->resetPassword($request);

        $this->failIfNeeded($result, 'Error reseteando contraseña');

        return $result['data'] ?? [];
    }

    public function setpassword(Request $request): array
    {
        $result = $this->service->setPassword($request);

        $this->failIfNeeded($result, 'Error actualizando contraseña');

        return $result['data'] ?? [];
    }

    public function logout(Request $request): array
    {
        $result = $this->service->logout($request);

        $this->failIfNeeded($result, 'Error cerrando sesión');

        return $result['data'] ?? [];
    }

    private function failIfNeeded(array $result, string $defaultMessage): void
    {
        if (($result['success'] ?? false) === false) {
            Response::fail(
                $result['code'] ?? 'AUTH_ERROR',
                $result['message'] ?? $defaultMessage,
                $result['status'] ?? 400,
                $result['meta'] ?? null
            );
        }
    }
}
