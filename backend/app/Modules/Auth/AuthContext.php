<?php
declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Http\Request;
use App\Modules\Auth\Repositories\AuthRepository;

final class AuthContext
{
    public function __construct(
        private ?AuthRepository $repository = null
    ) {
        $this->repository ??= new AuthRepository();
    }

    public function resolve(Request $request): array
    {
        $token = $this->resolveToken($request);

        if ($token === null) {
            return $this->fail(
                'VALIDATION_ERROR',
                'Debe enviar key_registro o un bearer token',
                422
            );
        }

        $session = $this->repository->findSessionByToken($token);
        if ($session === null) {
            return $this->fail(
                'TOKEN_NOT_FOUND',
                'La llave de sesión no es válida',
                401
            );
        }

        if (($session['estado'] ?? null) !== 'A') {
            return $this->fail(
                'SESSION_EXPIRED',
                'La session ha expirado.',
                401
            );
        }

        $userId = (int) ($session['usuario'] ?? 0);
        if ($userId <= 0) {
            return $this->fail(
                'SESSION_USER_NOT_FOUND',
                'La sesión no tiene un usuario válido asociado',
                401
            );
        }

        $user = $this->repository->findUserById($userId);
        if ($user === null) {
            return $this->fail(
                'USER_NOT_FOUND',
                'Usuario no existe en la base de datos',
                404
            );
        }

        $invoker = trim((string) $request->input('invoker', ''));
        $permissionRows = $this->repository->getPerfilRecursos(
            (int) ($user['id_perfil'] ?? 0),
            $invoker !== '' ? $invoker : null
        );

        if ($permissionRows === []) {
            return $this->fail(
                'USER_WITHOUT_PERMISSIONS',
                'Usuario sin permisos asignados',
                403
            );
        }

        $compactUser = [
            'id' => $user['ID'] ?? null,
            'nombre' => $user['nombreCompleto'] ?? null,
            'descripcion' => $user['descripcion'] ?? null,
            'img' => $user['img'] ?? null,
            'id_perfil' => $user['id_perfil'] ?? null,
            'nombre_perfil' => $user['Perf_Nombre'] ?? null,
            'change_pass' => $user['change_pass'] ?? null,
            'key_registro' => $token,
            'permisos' => $this->buildPermissionTree($permissionRows, 0),
        ];

        $fullUser = $user;
        $fullUser['key_registro'] = $token;
        $fullUser['permisos'] = $compactUser['permisos'];

        return [
            'success' => true,
            'status' => 200,
            'token' => $token,
            'session' => $session,
            'compact_user' => $compactUser,
            'full_user' => $fullUser,
        ];
    }

    private function resolveToken(Request $request): ?string
    {
        $candidates = [
            $request->input('key_registro'),
            $request->input('_llaveSession'),
            $request->header('x-session-token'),
            $request->bearerToken(),
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function buildPermissionTree(array $permissionRows, int $parentId): array
    {
        $children = [];

        foreach ($permissionRows as $row) {
            if ((int) ($row['padreId'] ?? 0) !== $parentId) {
                continue;
            }

            $resourceId = (int) ($row['idRecurso'] ?? 0);

            $children[] = [
                'id' => $row['idRecurso'] ?? null,
                'nombre_recurso' => $row['nombre_recurso'] ?? null,
                'display_nombre' => $row['display_nombre'] ?? null,
                'img' => $row['img'] ?? null,
                'idtipo' => $row['idtipo'] ?? null,
                'tipo' => $row['tipo'] ?? null,
                'estado' => $row['estado'] ?? null,
                'recursosHijos' => $this->buildPermissionTree($permissionRows, $resourceId),
                'direccion' => $this->splitArrayDir($row['arrayDir'] ?? null),
            ];
        }

        return array_values($children);
    }

    private function splitArrayDir(mixed $value): array
    {
        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $stringValue))));
    }

    private function fail(string $code, string $message, int $status): array
    {
        return [
            'success' => false,
            'code' => $code,
            'message' => $message,
            'status' => $status,
        ];
    }
}
