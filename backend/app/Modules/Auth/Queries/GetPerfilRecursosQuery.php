<?php

namespace App\Modules\Auth\Queries;

use App\Core\Database\QueryBuilder;

class GetPerfilRecursosQuery
{
    public function __construct(
        private int $idPerfil
        ,
        private ?string $invoker = null
    ) {
    }

    public function build(): QueryBuilder
    {
        $query = (new QueryBuilder())
            ->table('vw_perfil_recurso')
            ->select([
                'idperfil_recurso',
                'id_perfil',
                'idRecurso',
                'nombre_recurso',
                'estado',
                'img',
                'idtipo',
                'tipo',
                'arrayDir',
                'padreId',
                'display_nombre',
            ])
            ->where('id_perfil', '=', $this->idPerfil);

        if ($this->invoker !== null && trim($this->invoker) !== '') {
            $query->where('invoker', '=', trim($this->invoker));
        }

        return $query;
    }
}
