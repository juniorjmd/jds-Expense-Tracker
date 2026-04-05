<?php
declare(strict_types=1);

namespace App\Modules\Documentos\Services;

use App\Core\Http\Request;
use App\Modules\Documentos\Repositories\DocumentosRepository;

class DocumentosService
{
    public function __construct(
        private Request $request,
        private $authContext,
        private ?DocumentosRepository $repository = null
    ) {
    }

    public function listDocuments(int $userId, string $type = ''): array
    {
        $this->requireAuthenticatedUserId();

        return [[
            'documento_id' => 1,
            'nombre' => 'factura-001.pdf',
            'tipo' => $type ?: 'factura',
            'usuario_id' => $userId,
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]];
    }

    public function uploadDocument(int $userId, string $name, string $type, string $contentBase64): array
    {
        $this->requireAuthenticatedUserId();

        if (empty($name) || empty($type) || empty($contentBase64)) {
            throw new \RuntimeException('Faltan datos requeridos para subir el documento', 422);
        }

        return [
            'documento_id' => rand(100, 999),
            'nombre' => $name,
            'tipo' => $type,
            'usuario_id' => $userId,
            'url_descarga' => "https://example.com/download/{$name}",
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ];
    }

    public function downloadDocument(int $documentId): array
    {
        $this->requireAuthenticatedUserId();

        if ($documentId <= 0) {
            throw new \RuntimeException('ID de documento inválido', 422);
        }

        return [
            'documento_id' => $documentId,
            'nombre' => 'factura-001.pdf',
            'tipo' => 'factura',
            'download_url' => "https://example.com/download/{$documentId}",
            'usuario_id' => 1,
        ];
    }

    public function deleteDocument(int $documentId): array
    {
        $this->requireAuthenticatedUserId();

        if ($documentId <= 0) {
            throw new \RuntimeException('ID de documento inválido', 422);
        }

        return [
            'documento_id' => $documentId,
            'deleted' => true,
            'fecha_eliminacion' => date('Y-m-d H:i:s'),
        ];
    }

    public function getCurrentUserDocuments(bool $validateBox): array
    {
        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->getUserDocuments($userId, $validateBox);
        $documents = $this->decodeDocumentCollection($rows, 'objeto');

        return [
            'records' => $documents,
            'count' => count($documents),
        ];
    }

    public function createCurrentUserDocument(): array
    {
        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->createDocumentByUser($userId);

        return $this->normalizeDocumentCreationResult($rows, 'Documento de venta creado correctamente');
    }

    public function createCurrentUserPurchaseDocument(int $establishmentId): array
    {
        if ($establishmentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->createPurchaseDocumentByUser($userId, $establishmentId);

        return $this->normalizeDocumentCreationResult($rows, 'Documento de compra creado correctamente');
    }

    public function changeCurrentUserDocument(int $documentId): array
    {
        if ($documentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->changeActiveDocument($userId, $documentId);
        $firstRow = $this->assertProcedureSuccess($rows, 'cambiarDocumentoActual');

        return [
            'message' => $this->procedureMessage($firstRow, 'Documento activo actualizado correctamente'),
            'documentId' => $documentId,
        ];
    }

    public function changeCurrentUserPurchaseDocument(int $documentId): array
    {
        if ($documentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $this->requireAuthenticatedUserId();
        $rows = $this->repository()->changeActivePurchaseDocument($documentId);
        $firstRow = $this->assertProcedureSuccess($rows, 'cambiarDocumentoCompraActual');

        return [
            'message' => $this->procedureMessage($firstRow, 'Documento de compra activo actualizado correctamente'),
            'documentId' => $documentId,
        ];
    }

    private function requireAuthenticatedUserId(): int
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \RuntimeException(
                (string) ($authResult['message'] ?? 'Usuario no autenticado'),
                (int) ($authResult['status'] ?? 401)
            );
        }

        $userId = (int) ($authResult['compact_user']['id'] ?? $authResult['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new \RuntimeException('Usuario no autenticado', 401);
        }

        return $userId;
    }

    private function normalizeDocumentCreationResult(array $rows, string $defaultMessage): array
    {
        $firstRow = $this->assertProcedureSuccess($rows, 'crearDocumento');
        $documents = $this->decodeDocumentCollection($rows, 'OBJ');
        $documentId = isset($firstRow['idIngresado']) ? (int) $firstRow['idIngresado'] : $this->detectDocumentId($documents);

        return [
            'message' => $this->procedureMessage($firstRow, $defaultMessage),
            'documentId' => $documentId,
            'records' => $documents,
            'count' => count($documents),
        ];
    }

    private function assertProcedureSuccess(array $rows, string $procedureName): array
    {
        if ($rows === []) {
            throw new \RuntimeException("Error de datos, Procedimiento: {$procedureName} sin respuesta", 500);
        }

        $firstRow = $rows[0];
        $procedureResult = isset($firstRow['_result']) ? (int) $firstRow['_result'] : 100;

        if ($procedureResult !== 100) {
            $message = trim((string) ($firstRow['msg'] ?? ''));
            if ($message === '') {
                $message = "Error de datos, Procedimiento: {$procedureName}";
            }

            throw new \RuntimeException($message, 400);
        }

        return $firstRow;
    }

    private function decodeDocumentCollection(array $rows, string $jsonColumn): array
    {
        foreach ($rows as $row) {
            if (!isset($row[$jsonColumn])) {
                continue;
            }

            $decoded = json_decode((string) $row[$jsonColumn], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Error al decodificar JSON: ' . json_last_error_msg(), 500);
            }

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return [];
    }

    private function procedureMessage(array $row, string $defaultMessage): string
    {
        $message = trim((string) ($row['msg'] ?? ''));
        return $message !== '' ? $message : $defaultMessage;
    }

    private function detectDocumentId(array $documents): ?int
    {
        foreach ($documents as $document) {
            if ((int) ($document['estado'] ?? 0) === 1 && isset($document['orden'])) {
                return (int) $document['orden'];
            }
        }

        if (isset($documents[0]['orden'])) {
            return (int) $documents[0]['orden'];
        }

        return null;
    }

    private function repository(): DocumentosRepository
    {
        $this->repository ??= new DocumentosRepository();
        return $this->repository;
    }
}
