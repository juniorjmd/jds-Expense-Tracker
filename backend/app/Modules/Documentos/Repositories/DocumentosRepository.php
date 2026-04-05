<?php
declare(strict_types=1);

namespace App\Modules\Documentos\Repositories;

use App\Core\Database\BaseRepository;

class DocumentosRepository extends BaseRepository
{
    public function getUserDocuments(int $userId, bool $validateBox): array
    {
        return $this->callProcedure(
            'CALL getUserGenericDocuments(:idUser, :validarCaja)',
            [
                'idUser' => $userId,
                'validarCaja' => $validateBox ? 1 : 0,
            ]
        );
    }

    public function createDocumentByUser(int $userId): array
    {
        return $this->callProcedure(
            'CALL crearNuevoDocumento(:usuario)',
            ['usuario' => $userId]
        );
    }

    public function createPurchaseDocumentByUser(int $userId, int $establishmentId): array
    {
        return $this->callProcedure(
            'CALL crearNuevoDocumentoCompra(:usuario, :establecimiento)',
            [
                'usuario' => $userId,
                'establecimiento' => $establishmentId,
            ]
        );
    }

    public function changeActiveDocument(int $userId, int $documentId): array
    {
        return $this->callProcedure(
            'CALL cambiarDocumentoActual(:usuario, :_documento)',
            [
                'usuario' => $userId,
                '_documento' => $documentId,
            ]
        );
    }

    public function changeActivePurchaseDocument(int $documentId): array
    {
        return $this->callProcedure(
            'CALL cambiarDocumentoCompraActual(:_documento)',
            ['_documento' => $documentId]
        );
    }
}
