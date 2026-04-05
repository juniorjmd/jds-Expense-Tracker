<?php
declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Core\Database\BaseRepository;
use App\Modules\Auth\Queries\GetActiveUserByLoginQuery;
use App\Modules\Auth\Queries\GetPerfilRecursosQuery;
use App\Modules\Auth\Queries\GetUserByIdQuery;
use App\Modules\Auth\Queries\GetUserByTokenQuery;
use App\Modules\Auth\Queries\ValidateKeyQuery;

final class AuthRepository extends BaseRepository
{
    public function login(string $usuario, string $passwordSha1, string $llave): array
    {
        return $this->callProcedure(
            "CALL sp_login(:_usuario, :_pass, :_llave)",
            [
                '_usuario' => $usuario,
                '_pass' => $passwordSha1,
                '_llave' => $llave,
            ]
        );
    }

    public function getPerfilRecursos(int $idPerfil, ?string $invoker = null): array
    {
        $query = (new GetPerfilRecursosQuery($idPerfil, $invoker))->build();

        return $this->fetchAll(
            $query->toSql(),
            $query->getParams()
        );
    }

    public function actualizarCuotasVencidas(): void
    {
        $this->callProcedure("CALL sp_actualizar_cuotas_vencidas()");
    }

    public function findUserByToken(string $token): ?array
    {
        $query = (new ValidateKeyQuery($token))->build();

        return $this->fetchOne(
            $query->toSql(),
            $query->getParams()
        );
    }

    public function findSessionByToken(string $token): ?array
    {
        $query = (new GetUserByTokenQuery($token))->build();

        return $this->fetchOne(
            $query->toSql(),
            $query->getParams()
        );
    }

    public function findUserById(int $id): ?array
    {
        $query = (new GetUserByIdQuery($id))->build();

        return $this->fetchOne(
            $query->toSql(),
            $query->getParams()
        );
    }

    public function findUserByLogin(string $login): ?array
    {
        $query = (new GetActiveUserByLoginQuery($login))->build();

        return $this->fetchOne(
            $query->toSql(),
            $query->getParams()
        );
    }

    public function updatePassword(int $userId, string $passwordSha1, bool $changePass): bool
    {
        return $this->execute(
            "UPDATE `usuarios` SET `pass` = :pass, `change_pass` = :change_pass WHERE `ID` = :id",
            [
                'pass' => $passwordSha1,
                'change_pass' => $changePass ? 1 : 0,
                'id' => $userId,
            ]
        );
    }

    public function invalidateSessionByToken(string $token): bool
    {
        return $this->execute(
            "UPDATE `session` SET `estado` = (SELECT `idestado` FROM `estado_registro` WHERE `estado` = 'I' LIMIT 1) WHERE `key` = :token",
            [
                'token' => $token,
            ]
        );
    }
}
