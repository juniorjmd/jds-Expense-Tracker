<?php
declare(strict_types=1);

namespace App\Modules\Vehiculos\Services;

use App\Core\Http\Request;
use App\Modules\Vehiculos\Repositories\VehiculosRepository;

class VehiculosService
{
    public function __construct(
        private Request $request,
        private $authContext,
        private ?VehiculosRepository $repository = null
    ) {
        $this->repository ??= new VehiculosRepository();
    }

    public function createDocumentForVehicleService(array $payload): array
    {
        $authResult = $this->authContext->resolve($this->request);
        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        $requiredFields = [
            'placaVehiculo',
            'cod_servicio',
            'propietario',
            'cod_tipo_vehiculo',
            'lavador',
            'cajaAsignada',
            'valor',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '' || $payload[$field] === null) {
                throw new \Exception('Error de datos, faltan uno o mas valores para la consulta');
            }
        }

        $userId = (int) ($authResult['compact_user']['id'] ?? 0);
        if ($userId <= 0) {
            throw new \Exception('Usuario no autenticado');
        }

        $this->repository->beginTransaction();

        try {
            $documentId = (int) ($payload['idDocumento'] ?? 0);

            if ($documentId <= 0) {
                $procedureResult = $this->repository->createDocumentByVehicleIngress(
                    $userId,
                    (int) $payload['cajaAsignada'],
                    (string) $payload['placaVehiculo'],
                    (int) $payload['propietario']
                );

                if ($procedureResult === null) {
                    throw new \Exception('Error de datos, Procedimiento : crearNuevoDocumento');
                }

                if ((int) ($procedureResult['_result'] ?? 0) !== 100) {
                    throw new \Exception((string) ($procedureResult['msg'] ?? 'Error creando documento por ingreso de vehiculo'));
                }

                $documentId = (int) ($procedureResult['idIngresado'] ?? 0);
            }

            $box = $this->repository->getBoxById((int) $payload['cajaAsignada']);
            if ($box === null) {
                throw new \Exception('La caja relacionada no existe!!!');
            }

            $service = $this->repository->getServiceById((int) $payload['cod_servicio']);
            if ($service === null) {
                throw new \Exception('El servicio enviado no existe!!!');
            }

            $this->repository->insertVehicleIngress([
                'cod_servicio' => (int) $payload['cod_servicio'],
                'idCliente' => (int) $payload['propietario'],
                'cod_tipo_vehiculo' => (int) $payload['cod_tipo_vehiculo'],
                'empleadoId' => (int) $payload['lavador'],
                'cajaAsignada' => (int) $payload['cajaAsignada'],
                'idDocumento' => $documentId,
                'valor' => (float) $payload['valor'],
                'placaVehiculo' => (string) $payload['placaVehiculo'],
                'usuario_creacion' => $userId,
            ]);

            $warehouseId = (int) ($box['idBodegaStock'] ?? 0);
            $externalServiceId = trim((string) ($service['id_externo'] ?? ''));
            $productId = $externalServiceId !== '' ? $externalServiceId : 'SERV-' . (int) $payload['cod_servicio'];
            $existenceId = 999999999;

            if ($externalServiceId !== '') {
                $resolvedExistenceId = $this->repository->getProductExistenceId($warehouseId, $externalServiceId);
                if ($resolvedExistenceId === null) {
                    throw new \Exception('Error al obtener el id de la existencia actual del servicio!!!');
                }

                $existenceId = $resolvedExistenceId;
            }

            $serviceName = trim((string) ($service['nombre'] ?? ''));
            $productName = $serviceName . ' - Placa : ' . (string) $payload['placaVehiculo'];
            $value = (float) $payload['valor'];

            $this->repository->insertDocumentProduct([
                'orden' => $documentId,
                'idDocumento' => $documentId,
                'idProducto' => $productId,
                'nombreProducto' => $productName,
                'presioVenta' => $value,
                'porcent_iva' => 0,
                'presioSinIVa' => $value,
                'IVA' => 0,
                'cantidadVendida' => 1,
                'valorTotal' => $value,
                'usuario' => $userId,
                'cant_real_descontada' => 1,
                'id_existencia' => $existenceId,
                'estado_linea_venta' => 'S',
            ]);

            $this->repository->commitTransaction();

            return [
                'message' => 'Servicio vehicular ingresado correctamente',
                'idDocumento' => $documentId,
            ];
        } catch (\Throwable $e) {
            try {
                $this->repository->rollBackTransaction();
            } catch (\Throwable) {
            }

            throw $e;
        }
    }
}
