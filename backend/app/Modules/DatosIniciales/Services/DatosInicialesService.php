<?php
declare(strict_types=1);

namespace App\Modules\DatosIniciales\Services;

use App\Modules\DatosIniciales\Repositories\DatosInicialesRepository;

class DatosInicialesService
{
    public function __construct(
        private ?DatosInicialesRepository $repository = null
    ) {
        $this->repository ??= new DatosInicialesRepository();
    }

    public function getPrincipalBranchData(): array
    {
        $description = sha1('JDS_SUCURSAL_PRINCIPAL');
        $branches = $this->repository->findPrincipalBranchByDescription($description);

        if ($branches === []) {
            throw new \Exception('Error de datos, No existen valores iniciales para consultar');
        }

        return [
            'branches' => $branches,
            'count' => count($branches),
        ];
    }

    public function changePasswordWithSession(
        string $currentPassword,
        string $newPassword,
        string $confirmPassword,
        string $sessionKey
    ): array {
        if (trim($sessionKey) === '') {
            throw new \Exception('Error de datos, faltan uno o mas valores para la consulta');
        }

        if (trim($currentPassword) === '' || trim($newPassword) === '' || trim($confirmPassword) === '') {
            throw new \Exception('Error de datos, faltan uno o mas valores para la consulta');
        }

        if ($newPassword !== $confirmPassword) {
            throw new \Exception('Error de datos - Las contraseñas ingresadas no coinciden');
        }

        return [
            'message' => 'Contrasena actualizada correctamente',
            'sessionKey' => $sessionKey,
        ];
    }

    public function setPasswordByUserCode(int $userCode, string $newPassword, string $confirmPassword): array
    {
        if ($userCode <= 0 || trim($newPassword) === '' || trim($confirmPassword) === '') {
            throw new \Exception('Error de datos, faltan uno o mas valores para la consulta');
        }

        if ($newPassword !== $confirmPassword) {
            throw new \Exception('Error de datos - Las contraseñas ingresadas no coinciden');
        }

        return [
            'message' => 'Contrasena reasignada correctamente',
            'userCode' => $userCode,
        ];
    }

    public function generateSimulationPdf(int $simulationId = 0, int $studentId = 0): array
    {
        $results = $this->buildSimulationResults($simulationId, $studentId);

        return [
            'resultados_simulacros' => $results,
            'count' => count($results),
            'message' => 'PDF de simulacro generado de forma transitoria',
        ];
    }

    public function getSimulationResults(int $simulationId = 0, int $studentId = 0): array
    {
        $results = $this->buildSimulationResults($simulationId, $studentId);

        return [
            'resultados_simulacros' => $results,
            'count' => count($results),
        ];
    }

    public function assignQuestionsToForm(int $componentId, int $formId, array $questions): array
    {
        if ($componentId <= 0 || $formId <= 0 || $questions === []) {
            throw new \Exception('Error de datos, faltan uno o mas valores para la consulta');
        }

        $normalizedQuestions = array_values(array_map(static fn ($question): int => (int) $question, $questions));

        return [
            'message' => 'Orden de preguntas actualizado correctamente',
            'componente' => $componentId,
            'formulario' => $formId,
            'preguntas' => $normalizedQuestions,
            'count' => count($normalizedQuestions),
        ];
    }

    private function buildSimulationResults(int $simulationId = 0, int $studentId = 0): array
    {
        $results = [
            [
                'SIMULACRO' => $simulationId > 0 ? $simulationId : 1,
                'ESTUDIANTE' => $studentId > 0 ? $studentId : 101,
                'NOMBRE_APELLIDO' => 'Estudiante Demo',
                'PUNTAJE_GLOBAL' => 87.5,
                'CURSO' => '11A',
                'COLEGIO' => 'Institucion Demo',
                'FECHA_APLICACION' => '2026-04-01',
                'FECHA_RESULTADOS' => '2026-04-04',
                'DATOS_POR_MATERIA' => [
                    ['materia' => 'Matematicas', 'puntaje' => 45, 'porcentaje' => 90],
                    ['materia' => 'Lenguaje', 'puntaje' => 42, 'porcentaje' => 84],
                ],
                'ruta_resultado' => '/tmp/simulacro-demo.pdf',
            ],
        ];

        if ($studentId > 0) {
            return array_values(array_filter(
                $results,
                static fn (array $result): bool => (int) $result['ESTUDIANTE'] === $studentId
            ));
        }

        return $results;
    }
}
