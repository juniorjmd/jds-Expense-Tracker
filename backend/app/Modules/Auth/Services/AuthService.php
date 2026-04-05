<?php
declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;
use App\Modules\Auth\Repositories\AuthRepository;

final class AuthService
{
    public function __construct(
        private ?AuthRepository $repository = null,
        private ?AuthContext $authContext = null
    ) {
        $this->repository ??= new AuthRepository();
        $this->authContext ??= new AuthContext($this->repository);
    }

    public function login(Request $request): array
    {
        // Aceptar tanto 'usuario' como '_usuario' para compatibilidad con frontend legacy
        $usuario = trim((string) $request->input('usuario', $request->input('_usuario', '')));
        // Aceptar tanto 'password' como '_password' para compatibilidad con frontend legacy
        $password = (string) $request->input('password', $request->input('_password', ''));

        if ($usuario === '' || $password === '') {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Debe enviar usuario y password',
                'status' => 422,
            ];
        }

        $passwordSha1 = sha1($password);
        $llave = sha1($usuario . date('Ymdhms'));

        $result = $this->repository->login($usuario, $passwordSha1, $llave);

        if (empty($result)) {
            return [
                'success' => false,
                'code' => 'LOGIN_EMPTY_RESPONSE',
                'message' => 'No se obtuvo respuesta del procedimiento de login',
                'status' => 500,
            ];
        }

        $row = $result[0];
        $codigo = $row['_result'] ?? null;

        return match ($codigo) {
            '-1' => [
                'success' => false,
                'code' => 'LOGIN_INVALID_CREDENTIALS',
                'message' => 'Usuario o clave inválidos',
                'status' => 401,
            ],

            '-2' => [
                'success' => false,
                'code' => 'LOGIN_SESSION_SAVE_ERROR',
                'message' => 'Error al guardar la sesión',
                'status' => 500,
            ],

            '-3' => [
                'success' => false,
                'code' => 'LOGIN_NO_PERMISSIONS',
                'message' => 'Usuario sin permisos en el sistema',
                'status' => 403,
            ],

            '100' => $this->buildSuccessLoginResponse($row),

            default => [
                'success' => false,
                'code' => 'LOGIN_UNKNOWN_RESPONSE',
                'message' => 'Respuesta de login no controlada',
                'status' => 500,
                'meta' => [
                    '_result' => $codigo,
                ],
            ]
        };
    }

    public function validateKey(Request $request): array
    {
        $context = $this->authContext->resolve($request);

        if (($context['success'] ?? false) === false) {
            return $context;
        }

        return [
            'success' => true,
            'code' => 'TOKEN_OK',
            'message' => 'Llave válida',
            'status' => 200,
            'data' => [
                'usuario' => $context['compact_user'],
            ],
        ];
    }

    public function me(Request $request): array
    {
        $context = $this->authContext->resolve($request);

        if (($context['success'] ?? false) === false) {
            return $context;
        }

        return [
            'success' => true,
            'code' => 'AUTH_ME_OK',
            'message' => 'Usuario autenticado obtenido correctamente',
            'status' => 200,
            'data' => [
                'usuario' => $context['full_user'],
            ],
        ];
    }

    public function resetPassword(Request $request): array
    {
        $usuario = trim((string) $request->input('usuario', $request->input('_usuario', '')));

        if ($usuario === '') {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Debe enviar usuario',
                'status' => 422,
            ];
        }

        $user = $this->repository->findUserByLogin($usuario);

        if ($user === null) {
            return [
                'success' => false,
                'code' => 'USER_NOT_FOUND',
                'message' => 'Usuario invalido',
                'status' => 404,
            ];
        }

        $email = trim((string) ($user['mail'] ?? ''));
        if ($email === '' || strtoupper($email) === 'N-D') {
            return [
                'success' => false,
                'code' => 'USER_WITHOUT_EMAIL',
                'message' => 'La persona ingresada para usuario no posee correo electronico',
                'status' => 422,
            ];
        }

        $newPassword = $this->generatePassword();
        $updated = $this->repository->updatePassword(
            (int) $user['ID'],
            sha1($newPassword),
            false
        );

        if ($updated === false) {
            return [
                'success' => false,
                'code' => 'PASSWORD_RESET_FAILED',
                'message' => 'No fue posible resetear la contraseña',
                'status' => 500,
            ];
        }

        if (!$this->sendResetPasswordEmail($user, $newPassword)) {
            return [
                'success' => false,
                'code' => 'PASSWORD_RESET_EMAIL_FAILED',
                'message' => 'Error al enviar correo de notificacion',
                'status' => 500,
            ];
        }

        return [
            'success' => true,
            'code' => 'PASSWORD_RESET_OK',
            'message' => 'Contraseña reseteada correctamente',
            'status' => 200,
            'data' => [
                'usuarioID' => $user['ID'] ?? null,
                'mail' => $email,
            ],
        ];
    }

    public function setPassword(Request $request): array
    {
        $password = trim((string) $request->input('password', $request->input('pass', $request->input('_pass', ''))));
        $userId = (int) $request->input('id_usuario', $request->input('_id_usuario', 0));

        if ($password === '') {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Debe enviar password',
                'status' => 422,
            ];
        }

        if ($userId <= 0) {
            $context = $this->authContext->resolve($request);

            if (($context['success'] ?? false) === false) {
                return $context;
            }

            $userId = (int) ($context['full_user']['ID'] ?? 0);
        }

        if ($userId <= 0) {
            return [
                'success' => false,
                'code' => 'USER_NOT_FOUND',
                'message' => 'No fue posible identificar el usuario',
                'status' => 404,
            ];
        }

        $updated = $this->repository->updatePassword($userId, sha1($password), true);

        if ($updated === false) {
            return [
                'success' => false,
                'code' => 'PASSWORD_UPDATE_FAILED',
                'message' => 'No fue posible actualizar la contraseña',
                'status' => 500,
            ];
        }

        return [
            'success' => true,
            'code' => 'PASSWORD_UPDATE_OK',
            'message' => 'Contraseña actualizada correctamente',
            'status' => 200,
            'data' => [
                'usuarioID' => $userId,
            ],
        ];
    }

    public function logout(Request $request): array
    {
        $token = trim((string) $request->input('llaveSession', $request->input('_llaveSession', $request->input('key_registro', ''))));

        if ($token === '') {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Debe enviar una llave de session',
                'status' => 422,
            ];
        }

        $session = $this->repository->findSessionByToken($token);
        if ($session === null) {
            return [
                'success' => false,
                'code' => 'TOKEN_NOT_FOUND',
                'message' => 'La llave de session no es valida',
                'status' => 404,
            ];
        }

        $updated = $this->repository->invalidateSessionByToken($token);
        if ($updated === false) {
            return [
                'success' => false,
                'code' => 'LOGOUT_FAILED',
                'message' => 'No fue posible cerrar la sesión',
                'status' => 500,
            ];
        }

        return [
            'success' => true,
            'code' => 'LOGOUT_OK',
            'message' => 'Sesión cerrada correctamente',
            'status' => 200,
            'data' => [
                'key_registro' => $token,
                'session_status' => 'I',
            ],
        ];
    }

    private function buildSuccessLoginResponse(array $row): array
    {
        $this->repository->actualizarCuotasVencidas();

        $idPerfil = (int)($row['id_perfil'] ?? 0);
        $permisos = $this->repository->getPerfilRecursos($idPerfil);

        return [
            'success' => true,
            'code' => 'LOGIN_OK',
            'message' => 'Login exitoso',
            'status' => 200,
            'data' => [
                '_result' => '100',
                'usuario' => [
                    'id' => $row['ID'] ?? null,
                    'nombre' => $row['nombreCompleto'] ?? null,
                    'descripcion' => $row['descripcion'] ?? null,
                    'img' => $row['img'] ?? null,
                    'id_perfil' => $row['id_perfil'] ?? null,
                    'change_pass' => $row['change_pass'] ?? null,
                    'nombre_perfil' => $row['Perf_Nombre'] ?? null,
                    'key_registro' => $row['llave_session'] ?? null,
                    'permisos' => $this->buildFlatPermissions($permisos),
                ],
            ],
        ];
    }

    private function buildFlatPermissions(array $permissionRows): array
    {
        $permissions = [];

        foreach ($permissionRows as $value) {
            $permissions[] = [
                'id' => $value['idperfil_recurso'] ?? null,
                'id_perfil' => $value['id_perfil'] ?? null,
                'recurso' => [
                    'id' => $value['idRecurso'] ?? null,
                    'nombre_recurso' => $value['nombre_recurso'] ?? null,
                    'display_nombre' => $value['display_nombre'] ?? null,
                    'img' => $value['img'] ?? null,
                    'idtipo' => $value['idtipo'] ?? null,
                    'tipo' => $value['tipo'] ?? null,
                    'estado' => $value['estado'] ?? null,
                    'direccion' => $this->splitArrayDir($value['arrayDir'] ?? null),
                ],
            ];
        }

        return $permissions;
    }

    private function generatePassword(int $length = 15): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($characters) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }

    private function sendResetPasswordEmail(array $user, string $plainPassword): bool
    {
        $to = trim((string) ($user['mail'] ?? ''));
        if ($to === '') {
            return false;
        }

        $frontUrl = rtrim((string) ($_ENV['URL_FRONT'] ?? $_ENV['APP_FRONT_URL'] ?? ''), '/');
        $loginUrl = $frontUrl !== '' ? $frontUrl . '/login' : '/login';
        $name = (string) ($user['nombreCompleto'] ?? '');
        $login = (string) ($user['Login'] ?? '');
        $subject = 'Bienvenido a JDS - sofdla.com.co';
        $message = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Reset password</title></head><body>'
            . '<p>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Tu cuenta fue actualizada exitosamente.</p>'
            . '<p><strong>Usuario:</strong> ' . htmlspecialchars($login, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Contraseña:</strong> ' . htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p>Te recomendamos cambiar tu contraseña al iniciar sesión.</p>'
            . '<p><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '">Iniciar sesión</a></p>'
            . '</body></html>';

        $mailFrom = (string) ($_ENV['MAIL_FROM'] ?? 'j.dominguez@sofdla.com.co');
        $headers = "From: " . strip_tags($mailFrom) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($mailFrom) . "\r\n";
        $headers .= "CC: " . strip_tags($mailFrom) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($to, $subject, $message, $headers);
    }

    private function splitArrayDir(mixed $value): array
    {
        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $stringValue))));
    }
}
