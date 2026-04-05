<?php
declare(strict_types=1);

namespace App\Modules\DatosIniciales\Repositories;

use App\Core\Database\BaseRepository;

class DatosInicialesRepository extends BaseRepository
{
    public function findPrincipalBranchByDescription(string $description): array
    {
        return $this->fetchAll(
            'SELECT * FROM `vw_sucursales` WHERE `descripcion` = :description',
            ['description' => $description]
        );
    }
}
