<?php
declare(strict_types=1);

namespace App\Modules\Personas\Services;

use App\Core\Http\Request;

class PersonasService
{
    public function __construct(
        private Request $request,
        private $authContext
    ) {
    }

    public function searchOdooPersonTitle(): array
    {
        $this->ensureAuthenticated();

        // TODO: reemplazar por integración real con Odoo.
        return [
            'error' => 'ok',
            'data' => [
                [
                    'id' => 1,
                    'display_name' => 'Sr.'
                ],
                [
                    'id' => 2,
                    'display_name' => 'Sra.'
                ],
            ],
        ];
    }

    public function getClientMasters(): array
    {
        $this->ensureAuthenticated();

        // TODO: reemplazar por lecturas reales de BD.
        return [
            'error' => 'ok',
            'datos' => [
                'parametros' => [
                    'ID_PAIS_DEFAULT' => 1,
                    'ID_DEP_DEFAULT' => 11,
                    'ID_CIUDAD_DEFAULT' => 11001,
                    'ID_TIPO_ID_CEDULA' => 1,
                    'ID_TIPO_ID_NIT' => 2,
                ],
                'tipo_id_clientes' => [
                    ['id' => 1, 'nombre' => 'Cédula de ciudadanía'],
                    ['id' => 2, 'nombre' => 'NIT'],
                ],
                'empresas' => [
                    ['id' => 1, 'razon_social' => 'JDS Principal SAS'],
                ],
                'paises' => [
                    ['id' => 1, 'nombre' => 'Colombia'],
                ],
                'departamentos' => [
                    ['id' => 11, 'nombre' => 'Bogotá D.C.'],
                ],
                'ciudades' => [
                    ['id' => 11001, 'nombre' => 'Bogotá'],
                ],
            ],
        ];
    }

    private function ensureAuthenticated(): void
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }
    }
}
