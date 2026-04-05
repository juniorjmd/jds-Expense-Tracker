<?php
declare(strict_types=1);

namespace App\Modules\Admin;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Admin\Services\AdminService;

class AdminController
{
    private Request $request;
    private AdminService $service;

    public function __construct(
        Request $request,
        AdminService $service
    ) {
        $this->request = $request;
        $this->service = $service;
    }

    /**
     * Obtiene el arbol de recursos disponible.
     */
    public function getAllResources(): void
    {
        try {
            Response::ok($this->service->getAllResources());
        } catch (\Exception $e) {
            Response::fail('GET_ALL_RECURSOS_ERROR', $e->getMessage());
        }
    }

    /**
     * Obtiene el arbol de recursos marcado por perfil.
     */
    public function getAllResourcesByProfile(): void
    {
        try {
            $profileId = (int) $this->request->input('idPerfil', 0);
            Response::ok($this->service->getAllResourcesByProfile($profileId));
        } catch (\Exception $e) {
            Response::fail('GET_ALL_RECURSOS_BY_PERFIL_ERROR', $e->getMessage());
        }
    }

    /**
     * Actualiza recursos asignados a un perfil.
     */
    public function setProfileResources(): void
    {
        try {
            $profileId = (int) $this->request->input('perfil', 0);
            $resources = $this->request->input('recursos', []);

            Response::ok($this->service->setProfileResources($profileId, $resources));
        } catch (\Exception $e) {
            Response::fail('SET_PERFIL_RECURSO_ERROR', $e->getMessage());
        }
    }

    /**
     * Obtiene lista de usuarios del sistema
     * Parámetros: _estado (opcional)
     */
    public function getUsers(): void
    {
        try {
            $estado = $this->request->input('estado', 'A');
            $result = $this->service->getUsers($estado);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('GET_USERS_ERROR', $e->getMessage());
        }
    }

    /**
     * Crea un nuevo usuario
     * Parámetros: _login, _nombre1, _apellido1, _mail, _id_perfil (opcional)
     */
    public function createUser(): void
    {
        try {
            $payload = $this->request->input('arraydatos', []);
            $payload = is_array($payload) ? $payload : [];

            $userData = [
                'idPersona' => (int) ($payload['idPersona'] ?? $this->request->input('idPersona', 0)),
                'Login' => (string) ($payload['Login'] ?? $this->request->input('Login', $this->request->input('login', ''))),
                'Nombre1' => (string) ($payload['Nombre1'] ?? $this->request->input('Nombre1', $this->request->input('nombre1', ''))),
                'Nombre2' => (string) ($payload['Nombre2'] ?? $this->request->input('Nombre2', '')),
                'Apellido1' => (string) ($payload['Apellido1'] ?? $this->request->input('Apellido1', $this->request->input('apellido1', ''))),
                'Apellido2' => (string) ($payload['Apellido2'] ?? $this->request->input('Apellido2', '')),
                'email' => (string) ($payload['email'] ?? $this->request->input('mail', $this->request->input('email', ''))),
                'estado' => (int) ($payload['estado'] ?? $this->request->input('estado', 1)),
                'libranza' => (int) ($payload['libranza'] ?? $this->request->input('libranza', 0)),
                'usr_registro' => (int) ($payload['usr_registro'] ?? $this->request->input('usr_registro', 0)),
            ];

            $result = $this->service->createUser($userData);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('CREATE_USER_ERROR', $e->getMessage());
        }
    }

    /**
     * Actualiza información de usuario
     * Parámetros: _id, _estado, _id_perfil (opcional)
     */
    public function updateUser(): void
    {
        try {
            $userId = (int) $this->request->input('id', 0);
            $updateData = [
                'estado' => $this->request->input('estado', ''),
                'id_perfil' => (int) $this->request->input('id_perfil', 0)
            ];

            $result = $this->service->updateUser($userId, $updateData);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('UPDATE_USER_ERROR', $e->getMessage());
        }
    }

    /**
     * Obtiene estructura de menús del sistema
     * Parámetros: _id_perfil (opcional)
     */
    public function getMenus(): void
    {
        try {
            $perfilId = (int) $this->request->input('id_perfil', 0);
            $result = $this->service->getMenus($perfilId);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('GET_MENUS_ERROR', $e->getMessage());
        }
    }

    /**
     * Crea una operacion contable manual.
     */
    public function createManualOperation(): void
    {
        try {
            $operation = $this->request->input('operacion', []);
            Response::ok($this->service->createManualOperation(is_array($operation) ? $operation : []));
        } catch (\Exception $e) {
            Response::fail('CREAR_OPERACION_MANUAL_ERROR', $e->getMessage());
        }
    }

    /**
     * Crea o actualiza una operacion preestablecida.
     */
    public function createPresetOperation(): void
    {
        try {
            $payload = $this->request->input('arraydatos', []);
            Response::ok($this->service->createPresetOperation(is_array($payload) ? $payload : []));
        } catch (\Exception $e) {
            Response::fail('CREAR_OPERACIONES_PREESTABLECIDAS_ERROR', $e->getMessage());
        }
    }

    /**
     * Ejecuta una operacion preestablecida.
     */
    public function executePresetOperation(): void
    {
        try {
            $payload = $this->request->input('arraydatos', []);
            Response::ok($this->service->executePresetOperation(is_array($payload) ? $payload : []));
        } catch (\Exception $e) {
            Response::fail('EJECUTAR_OPERACIONES_PREESTABLECIDAS_ERROR', $e->getMessage());
        }
    }
}
