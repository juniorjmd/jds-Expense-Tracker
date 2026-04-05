<?php
declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Core\Http\Request;

class AdminService
{
    private Request $request;
    private $authContext;

    public function __construct(Request $request, $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    /**
     * Obtiene el arbol base de recursos del sistema.
     */
    public function getAllResources(): array
    {
        $this->assertAuthenticated();

        $resources = $this->buildBaseResources();

        return [
            'resources' => $resources,
            'count' => $this->countResources($resources),
        ];
    }

    /**
     * Obtiene recursos marcados para un perfil.
     */
    public function getAllResourcesByProfile(int $profileId): array
    {
        $this->assertAuthenticated();

        if ($profileId <= 0) {
            throw new \Exception('Perfil invalido');
        }

        $selectedIds = match ($profileId) {
            1 => [1, 2, 3, 4, 5, 6, 7, 8],
            2 => [1, 2, 4, 5],
            default => [1],
        };

        $resources = $this->applySelectedResources($this->buildBaseResources(), $selectedIds);

        return [
            'profileId' => $profileId,
            'resources' => $resources,
            'count' => $this->countResources($resources),
        ];
    }

    /**
     * Guarda la seleccion de recursos para un perfil.
     */
    public function setProfileResources(int $profileId, array $resourceTree): array
    {
        $this->assertAuthenticated();

        if ($profileId <= 0) {
            throw new \Exception('Faltan valores para ingresar los permisos al perfil!');
        }

        if ($resourceTree === []) {
            throw new \Exception('Faltan valores para ingresar los permisos al perfil!');
        }

        $selectedIds = $this->collectSelectedResourceIds($resourceTree);

        return [
            'profileId' => $profileId,
            'selectedResourceIds' => $selectedIds,
            'updatedCount' => count($selectedIds),
            'message' => 'Permisos actualizados correctamente',
        ];
    }

    /**
     * Obtiene lista de usuarios filtrados por estado
     */
    public function getUsers(string $estado = 'A'): array
    {
        $this->assertAuthenticated();

        return [
            [
                'ID' => 1,
                'Login' => 'admin',
                'Nombre' => 'Administrador del Sistema',
                'estado' => 'A',
                'mail' => 'admin@jds.com'
            ],
            [
                'ID' => 2,
                'Login' => 'user1',
                'Nombre' => 'Usuario Ejemplo',
                'estado' => $estado,
                'mail' => 'user1@jds.com'
            ]
        ];
    }

    /**
     * Crea un nuevo usuario en el sistema
     */
    public function createUser(array $userData): array
    {
        $this->assertAuthenticated();

        if (($userData['idPersona'] ?? 0) <= 0) {
            throw new \Exception('La persona ingresada para usuario no existe!');
        }

        if (trim((string) ($userData['Login'] ?? '')) === '') {
            throw new \Exception('Debe ingresar el usuario para inicio de sesion');
        }

        $email = trim((string) ($userData['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('La persona ingresada para usuario no posee correo electronico!');
        }

        $userId = 1000 + (int) $userData['idPersona'];
        $fullName = trim(sprintf(
            '%s %s %s %s',
            (string) ($userData['Nombre1'] ?? ''),
            (string) ($userData['Nombre2'] ?? ''),
            (string) ($userData['Apellido1'] ?? ''),
            (string) ($userData['Apellido2'] ?? '')
        ));

        return [
            'usuarioID' => $userId,
            'message' => 'Usuario creado correctamente',
            'usuario' => [
                'ID' => $userId,
                'Login' => (string) $userData['Login'],
                'Nombre' => $fullName,
                'email' => $email,
                'estado' => (int) ($userData['estado'] ?? 1),
                'libranza' => (int) ($userData['libranza'] ?? 0),
                'idPersona' => (int) $userData['idPersona'],
            ],
        ];
    }

    /**
     * Actualiza información de un usuario existente
     */
    public function updateUser(int $userId, array $updateData): array
    {
        $this->assertAuthenticated();

        if ($userId <= 0) {
            throw new \Exception('ID de usuario inválido');
        }

        return [
            'ID' => $userId,
            'estado' => $updateData['estado'] ?: 'A',
            'id_perfil' => (int) ($updateData['id_perfil'] ?? 1) ?: 1,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
            'message' => 'Usuario actualizado correctamente',
        ];
    }

    /**
     * Obtiene estructura de menús del sistema
     */
    public function getMenus(int $perfilId = 0): array
    {
        $this->assertAuthenticated();

        // TODO: Implementar con BD cuando esté disponible
        // Simulación de estructura de menús
        return [
            [
                'idmenus' => 1,
                'Nombre' => 'Dashboard',
                'Descripcion' => 'Panel principal',
                'PadreId' => null,
                'Icono' => 'dashboard',
                'Url' => '/dashboard',
                'Orden' => 1
            ],
            [
                'idmenus' => 2,
                'Nombre' => 'Usuarios',
                'Descripcion' => 'Gestión de usuarios',
                'PadreId' => null,
                'Icono' => 'users',
                'Url' => '/admin/users',
                'Orden' => 2
            ],
            [
                'idmenus' => 3,
                'Nombre' => 'Carwash',
                'Descripcion' => 'Módulo de lavado',
                'PadreId' => null,
                'Icono' => 'car',
                'Url' => '/carwash',
                'Orden' => 3
            ]
        ];
    }

    /**
     * Crea una operacion contable manual desde el modulo admin.
     */
    public function createManualOperation(array $operation): array
    {
        $this->assertAuthenticated();

        if (trim((string) ($operation['nombre'] ?? '')) === '') {
            throw new \Exception('debe ingresar el nombre de la operacion');
        }

        return [
            'operationId' => 9001,
            'message' => 'Operacion manual creada correctamente',
            'operation' => [
                'nombre' => (string) $operation['nombre'],
                'descripcion' => (string) ($operation['descripcion'] ?? ''),
                'idPersona' => (int) ($operation['idPersona'] ?? 0),
                'totalDebito' => (float) ($operation['totalDebito'] ?? 0),
                'totalCredito' => (float) ($operation['totalCredito'] ?? 0),
                'fechaOperacion' => (string) ($operation['fechaOperacion'] ?? date('Y-m-d')),
            ],
        ];
    }

    /**
     * Crea o actualiza una operacion preestablecida.
     */
    public function createPresetOperation(array $payload): array
    {
        $this->assertAuthenticated();

        $accounts = $payload['cuentas'] ?? [];
        if (!is_array($accounts) || $accounts === []) {
            throw new \Exception('No existen cuentas para agregar a la tranferencia');
        }

        $operationId = (int) ($payload['id'] ?? 0);
        if ($operationId <= 0) {
            $operationId = 7001;
        }

        return [
            'message' => 'Operacion preestablecida guardada correctamente',
            'objeto' => [
                'id' => $operationId,
                'nombre' => (string) ($payload['nombre'] ?? ''),
                'descripcion' => (string) ($payload['descripcion'] ?? ''),
                'tipo' => (string) ($payload['tipo'] ?? ''),
                'cuentas' => array_values($accounts),
            ],
        ];
    }

    /**
     * Ejecuta una operacion preestablecida.
     */
    public function executePresetOperation(array $payload): array
    {
        $this->assertAuthenticated();

        $accounts = $payload['cuentas'] ?? [];
        if (!is_array($accounts) || $accounts === []) {
            throw new \Exception('No existen cuentas para agregar a la tranferencia');
        }

        return [
            'message' => 'Operacion preestablecida ejecutada correctamente',
            'objeto' => [
                'idOperacion' => 8001,
                'nombre' => (string) ($payload['nombre'] ?? 'Traslado'),
                'tipo' => (string) ($payload['tipo'] ?? ''),
                'cuentas' => array_values($accounts),
                'idEstablecimiento' => (int) ($payload['idEstablecimiento'] ?? 0),
            ],
        ];
    }

    private function assertAuthenticated(): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        return $authResult;
    }

    private function buildBaseResources(): array
    {
        return [
            [
                'id' => 1,
                'idPadre' => [],
                'nombre_recurso' => 'dashboard',
                'display_nombre' => 'Dashboard',
                'img' => 'dashboard',
                'idtipo' => 1,
                'tipo' => 'menu',
                'estado' => 'A',
                'seleccionado' => false,
                'direccion' => ['/dashboard'],
                'recursosHijos' => [],
            ],
            [
                'id' => 2,
                'idPadre' => [],
                'nombre_recurso' => 'admin',
                'display_nombre' => 'Administracion',
                'img' => 'admin_panel_settings',
                'idtipo' => 1,
                'tipo' => 'menu',
                'estado' => 'A',
                'seleccionado' => false,
                'direccion' => ['/admin'],
                'recursosHijos' => [
                    [
                        'id' => 3,
                        'idPadre' => [2],
                        'nombre_recurso' => 'usuarios',
                        'display_nombre' => 'Usuarios',
                        'img' => 'group',
                        'idtipo' => 2,
                        'tipo' => 'submenu',
                        'estado' => 'A',
                        'seleccionado' => false,
                        'direccion' => ['/admin', '/usuarios'],
                        'recursosHijos' => [],
                    ],
                    [
                        'id' => 4,
                        'idPadre' => [2],
                        'nombre_recurso' => 'perfiles',
                        'display_nombre' => 'Perfiles',
                        'img' => 'badge',
                        'idtipo' => 2,
                        'tipo' => 'submenu',
                        'estado' => 'A',
                        'seleccionado' => false,
                        'direccion' => ['/admin', '/perfiles'],
                        'recursosHijos' => [],
                    ],
                    [
                        'id' => 5,
                        'idPadre' => [2],
                        'nombre_recurso' => 'contabilidad',
                        'display_nombre' => 'Traslados contables',
                        'img' => 'swap_horiz',
                        'idtipo' => 2,
                        'tipo' => 'submenu',
                        'estado' => 'A',
                        'seleccionado' => false,
                        'direccion' => ['/admin', '/traslados-contables'],
                        'recursosHijos' => [
                            [
                                'id' => 6,
                                'idPadre' => [2, 5],
                                'nombre_recurso' => 'operaciones',
                                'display_nombre' => 'Operaciones',
                                'img' => 'calculate',
                                'idtipo' => 3,
                                'tipo' => 'accion',
                                'estado' => 'A',
                                'seleccionado' => false,
                                'direccion' => ['/admin', '/traslados-contables', '/operaciones'],
                                'recursosHijos' => [],
                            ],
                            [
                                'id' => 7,
                                'idPadre' => [2, 5],
                                'nombre_recurso' => 'ejecutar_traslado',
                                'display_nombre' => 'Ejecutar traslados',
                                'img' => 'play_arrow',
                                'idtipo' => 3,
                                'tipo' => 'accion',
                                'estado' => 'A',
                                'seleccionado' => false,
                                'direccion' => ['/admin', '/traslados-contables', '/ejecutar'],
                                'recursosHijos' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 8,
                'idPadre' => [],
                'nombre_recurso' => 'inventario',
                'display_nombre' => 'Inventario',
                'img' => 'inventory',
                'idtipo' => 1,
                'tipo' => 'menu',
                'estado' => 'A',
                'seleccionado' => false,
                'direccion' => ['/inventario'],
                'recursosHijos' => [],
            ],
        ];
    }

    private function applySelectedResources(array $resources, array $selectedIds): array
    {
        foreach ($resources as $index => $resource) {
            $resource['seleccionado'] = in_array($resource['id'], $selectedIds, true);

            if (!empty($resource['recursosHijos'])) {
                $resource['recursosHijos'] = $this->applySelectedResources($resource['recursosHijos'], $selectedIds);
                $resource['seleccionado'] = $resource['seleccionado']
                    || $this->hasSelectedChildren($resource['recursosHijos']);
            }

            $resources[$index] = $resource;
        }

        return $resources;
    }

    private function hasSelectedChildren(array $resources): bool
    {
        foreach ($resources as $resource) {
            if (($resource['seleccionado'] ?? false) === true) {
                return true;
            }
        }

        return false;
    }

    private function collectSelectedResourceIds(array $resources): array
    {
        $selectedIds = [];

        foreach ($resources as $resource) {
            if (($resource['seleccionado'] ?? false) === true && isset($resource['id'])) {
                $selectedIds[] = (int) $resource['id'];
            }

            if (!empty($resource['recursosHijos']) && is_array($resource['recursosHijos'])) {
                $selectedIds = [...$selectedIds, ...$this->collectSelectedResourceIds($resource['recursosHijos'])];
            }
        }

        return array_values(array_unique($selectedIds));
    }

    private function countResources(array $resources): int
    {
        $count = 0;

        foreach ($resources as $resource) {
            $count++;
            if (!empty($resource['recursosHijos'])) {
                $count += $this->countResources($resource['recursosHijos']);
            }
        }

        return $count;
    }
}
