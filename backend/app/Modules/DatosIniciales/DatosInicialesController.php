<?php
declare(strict_types=1);

namespace App\Modules\DatosIniciales;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\DatosIniciales\Services\DatosInicialesService;

class DatosInicialesController
{
    public function __construct(
        private Request $request,
        private ?DatosInicialesService $service = null
    ) {
        $this->service ??= new DatosInicialesService();
    }

    public function getPrincipalBranchData(): void
    {
        try {
            Response::ok($this->service->getPrincipalBranchData());
        } catch (\Throwable $e) {
            Response::fail('GET_SUCURSAL_PRINCIPAL_DATA_ERROR', $e->getMessage(), 500);
        }
    }

    public function changePasswordWithSession(): void
    {
        try {
            $result = $this->service->changePasswordWithSession(
                (string) $this->request->input('poikmjuy', ''),
                (string) $this->request->input('wsxedc', ''),
                (string) $this->request->input('kjhgtyuhybv', ''),
                (string) $this->request->input('llaveSession', '')
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('CHANGE_PASSWORD_WITH_SESSION_ERROR', $e->getMessage(), 400);
        }
    }

    public function setPasswordByUserCode(): void
    {
        try {
            $result = $this->service->setPasswordByUserCode(
                (int) $this->request->input('qazxswe', 0),
                (string) $this->request->input('wsxedc', ''),
                (string) $this->request->input('kjhgtyuhybv', '')
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('SET_PASSWORD_BY_USER_CODE_ERROR', $e->getMessage(), 400);
        }
    }

    public function generateSimulationPdf(): void
    {
        try {
            $result = $this->service->generateSimulationPdf(
                (int) $this->request->input('r1548juy', 0),
                (int) $this->request->input('85247efg', 0)
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('GENERATE_SIMULATION_PDF_ERROR', $e->getMessage(), 400);
        }
    }

    public function getSimulationResults(): void
    {
        try {
            $result = $this->service->getSimulationResults(
                (int) $this->request->input('r1548juy', 0),
                (int) $this->request->input('85247efg', 0)
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('GET_SIMULATION_RESULTS_ERROR', $e->getMessage(), 400);
        }
    }

    public function assignQuestionsToForm(): void
    {
        try {
            $questions = $this->request->input('preguntas', []);
            $result = $this->service->assignQuestionsToForm(
                (int) $this->request->input('componente', 0),
                (int) $this->request->input('formulario', 0),
                is_array($questions) ? $questions : []
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('ASSIGN_QUESTIONS_TO_FORM_ERROR', $e->getMessage(), 400);
        }
    }
}
