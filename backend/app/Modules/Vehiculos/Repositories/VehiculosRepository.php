<?php
declare(strict_types=1);

namespace App\Modules\Vehiculos\Repositories;

use App\Core\Database\BaseRepository;

class VehiculosRepository extends BaseRepository
{
    public function beginTransaction(): bool
    {
        return $this->beginTransactionInternal();
    }

    public function commitTransaction(): bool
    {
        return $this->commitInternal();
    }

    public function rollBackTransaction(): bool
    {
        return $this->rollBackInternal();
    }

    public function getBoxById(int $boxId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM `vw_cajas` WHERE `id` = :id LIMIT 1',
            ['id' => $boxId]
        );
    }

    public function createDocumentByVehicleIngress(
        int $userId,
        int $boxId,
        string $plate,
        int $clientId
    ): ?array {
        $result = $this->callProcedure(
            'CALL crearNuevoDocumentoPorIngresoVehiculo(:usuario, :cajaId, :infoAdicional, :cliente)',
            [
                'usuario' => $userId,
                'cajaId' => $boxId,
                'infoAdicional' => $plate,
                'cliente' => $clientId,
            ]
        );

        return $result[0] ?? null;
    }

    public function insertVehicleIngress(array $payload): bool
    {
        return $this->execute(
            'INSERT INTO `mov_vehiculos_ingreso_servicios`
            (`cod_servicio`, `idCliente`, `cod_tipo_vehiculo`, `empleadoId`, `cajaAsignada`, `idDocumento`, `valor`, `placaVehiculo`, `usuario_creacion`)
            VALUES
            (:cod_servicio, :idCliente, :cod_tipo_vehiculo, :empleadoId, :cajaAsignada, :idDocumento, :valor, :placaVehiculo, :usuario_creacion)',
            $payload
        );
    }

    public function getServiceById(int $serviceId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM `inv_mst_servicios` WHERE `id` = :id LIMIT 1',
            ['id' => $serviceId]
        );
    }

    public function getProductExistenceId(int $warehouseId, string $productId): ?int
    {
        $row = $this->fetchOne(
            'SELECT `id` FROM `inv_mst_producto_existencias`
            WHERE `id_bodega` = :warehouseId AND `id_producto` = :productId
            LIMIT 1',
            [
                'warehouseId' => $warehouseId,
                'productId' => $productId,
            ]
        );

        return $row !== null ? (int) $row['id'] : null;
    }

    public function insertDocumentProduct(array $payload): bool
    {
        return $this->execute(
            'INSERT INTO `documentos_listado_productos`
            (`orden`, `idDocumento`, `idProducto`, `nombreProducto`, `presioVenta`, `porcent_iva`, `presioSinIVa`, `IVA`, `cantidadVendida`, `valorTotal`, `usuario`, `cant_real_descontada`, `id_existencia`, `estado_linea_venta`)
            VALUES
            (:orden, :idDocumento, :idProducto, :nombreProducto, :presioVenta, :porcent_iva, :presioSinIVa, :IVA, :cantidadVendida, :valorTotal, :usuario, :cant_real_descontada, :id_existencia, :estado_linea_venta)',
            $payload
        );
    }

    protected function beginTransactionInternal(): bool
    {
        return parent::beginTransaction();
    }

    protected function commitInternal(): bool
    {
        return parent::commit();
    }

    protected function rollBackInternal(): bool
    {
        return parent::rollBack();
    }
}
