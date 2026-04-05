<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Documentos\Repositories\DocumentosRepository;
use App\Modules\Documentos\Services\DocumentosService;

class DocumentosServiceAuthContextStub
{
    public function __construct(private bool $success = true)
    {
    }

    public function resolve(Request $request): array
    {
        return $this->success
            ? ['success' => true, 'compact_user' => ['id' => 123]]
            : ['success' => false, 'message' => 'Usuario no autenticado', 'status' => 401];
    }
}

class DocumentosRepositoryStub extends DocumentosRepository
{
    public function __construct(
        private array $userDocuments = [],
        private array $createdDocuments = [],
        private array $createdPurchaseDocuments = [],
        private array $changedDocuments = [],
        private array $changedPurchaseDocuments = []
    ) {
    }

    public function getUserDocuments(int $userId, bool $validateBox): array
    {
        return $this->userDocuments;
    }

    public function createDocumentByUser(int $userId): array
    {
        return $this->createdDocuments;
    }

    public function createPurchaseDocumentByUser(int $userId, int $establishmentId): array
    {
        return $this->createdPurchaseDocuments;
    }

    public function changeActiveDocument(int $userId, int $documentId): array
    {
        return $this->changedDocuments;
    }

    public function changeActivePurchaseDocument(int $documentId): array
    {
        return $this->changedPurchaseDocuments;
    }
}

class DocumentosServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DOCUMENTOS SERVICE TEST ==========\n\n";

        $this->testListDocumentsWithValidAuth();
        $this->testListDocumentsWithInvalidAuth();
        $this->testUploadDocumentWithValidData();
        $this->testUploadDocumentWithMissingData();
        $this->testDownloadDocumentWithValidId();
        $this->testDeleteDocumentWithInvalidId();
        $this->testGetCurrentUserDocumentsReturnsStandardCollection();
        $this->testCreateCurrentUserDocumentParsesProcedurePayload();
        $this->testChangeCurrentUserDocumentReturnsMessage();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function service(
        bool $success = true,
        ?DocumentosRepository $repository = null
    ): DocumentosService {
        return new DocumentosService(
            new Request('POST', [], [], [], []),
            new DocumentosServiceAuthContextStub($success),
            $repository
        );
    }

    private function testListDocumentsWithValidAuth(): void
    {
        echo "TEST 1: listDocuments returns data with auth... ";

        try {
            $result = $this->service()->listDocuments(123, 'factura');
            if (!is_array($result) || ($result[0]['tipo'] ?? '') !== 'factura') {
                throw new \Exception('invalid list result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testListDocumentsWithInvalidAuth(): void
    {
        echo "TEST 2: listDocuments requires auth... ";

        try {
            $this->service(false)->listDocuments(123, 'factura');
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Usuario no autenticado') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testUploadDocumentWithValidData(): void
    {
        echo "TEST 3: uploadDocument returns metadata... ";

        try {
            $result = $this->service()->uploadDocument(123, 'test.pdf', 'factura', 'base64data');
            if (($result['nombre'] ?? '') !== 'test.pdf') {
                throw new \Exception('invalid upload result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testUploadDocumentWithMissingData(): void
    {
        echo "TEST 4: uploadDocument validates required data... ";

        try {
            $this->service()->uploadDocument(123, '', 'factura', '');
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Faltan datos requeridos para subir el documento') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testDownloadDocumentWithValidId(): void
    {
        echo "TEST 5: downloadDocument returns metadata... ";

        try {
            $result = $this->service()->downloadDocument(456);
            if (($result['documento_id'] ?? 0) !== 456) {
                throw new \Exception('invalid download result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testDeleteDocumentWithInvalidId(): void
    {
        echo "TEST 6: deleteDocument rejects invalid id... ";

        try {
            $this->service()->deleteDocument(0);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'ID de documento inválido') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testGetCurrentUserDocumentsReturnsStandardCollection(): void
    {
        echo "TEST 7: getCurrentUserDocuments returns standard records/count... ";

        try {
            $repository = new DocumentosRepositoryStub(
                userDocuments: [[
                    'objeto' => json_encode([
                        ['orden' => 10, 'estado' => 1],
                        ['orden' => 11, 'estado' => 0],
                    ], JSON_UNESCAPED_UNICODE),
                ]]
            );

            $result = $this->service(true, $repository)->getCurrentUserDocuments(true);

            if (($result['count'] ?? 0) !== 2 || ($result['records'][0]['orden'] ?? 0) !== 10) {
                throw new \Exception('invalid document collection');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateCurrentUserDocumentParsesProcedurePayload(): void
    {
        echo "TEST 8: createCurrentUserDocument parses procedure payload... ";

        try {
            $repository = new DocumentosRepositoryStub(
                createdDocuments: [[
                    '_result' => 100,
                    'msg' => 'Documento creado',
                    'idIngresado' => 44,
                    'OBJ' => json_encode([
                        ['orden' => 44, 'estado' => 1],
                    ], JSON_UNESCAPED_UNICODE),
                ]]
            );

            $result = $this->service(true, $repository)->createCurrentUserDocument();

            if (($result['documentId'] ?? 0) !== 44 || ($result['count'] ?? 0) !== 1) {
                throw new \Exception('invalid creation payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testChangeCurrentUserDocumentReturnsMessage(): void
    {
        echo "TEST 9: changeCurrentUserDocument returns message... ";

        try {
            $repository = new DocumentosRepositoryStub(
                changedDocuments: [[
                    '_result' => 100,
                    'msg' => 'Documento cambiado',
                ]]
            );

            $result = $this->service(true, $repository)->changeCurrentUserDocument(55);

            if (($result['documentId'] ?? 0) !== 55 || ($result['message'] ?? '') !== 'Documento cambiado') {
                throw new \Exception('invalid change payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function printSummary(): void
    {
        $total = $this->passCount + $this->failCount;
        echo "\n========== RESULT ==========\n";
        echo "PASSED: {$this->passCount}/{$total}\n";
        echo "FAILED: {$this->failCount}/{$total}\n";
        echo "===========================\n\n";
    }
}

(new DocumentosServiceTest())->run();
