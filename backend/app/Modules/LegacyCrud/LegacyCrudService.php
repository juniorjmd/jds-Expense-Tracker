<?php
declare(strict_types=1);

namespace App\Modules\LegacyCrud;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;
use RuntimeException;

final class LegacyCrudService
{
    public function __construct(
        private Request $request,
        private LegacyCrudRepository $repository,
        private AuthContext $authContext
    ) {}

    public function select(): array
    {
        $rows = $this->repository->select(
            table: $this->table(),
            columns: $this->columns(),
            where: $this->normalizeWhere($this->request->input('_where', [])),
            orderBy: $this->normalizeOrderBy($this->request->input('_orderBy', [])),
            limit: $this->normalizeLimit($this->request->input('_limit'))
        );

        return $this->formatRows($rows);
    }

    public function selectByLoggedUser(): array
    {
        $context = $this->requireUser();
        $column = (string) $this->request->input('_columnaUsuario', 'usuario');
        $where = $this->normalizeWhere($this->request->input('_where', []));
        $where[] = [
            'columna' => $column,
            'tipocomp' => '=',
            'dato' => (int) ($context['full_user']['ID'] ?? 0),
        ];

        $rows = $this->repository->select(
            table: $this->table(),
            columns: $this->columns(),
            where: $where,
            orderBy: $this->normalizeOrderBy($this->request->input('_orderBy', [])),
            limit: $this->normalizeLimit($this->request->input('_limit'))
        );

        return $this->formatRows($rows);
    }

    public function selectMany(): array
    {
        $tables = $this->request->input('_tablas', []);
        if (!is_array($tables) || $tables === []) {
            throw new RuntimeException('Debe enviar _tablas');
        }

        $results = [];
        foreach ($tables as $table) {
            $results[] = $this->repository->select((string) $table);
        }

        return [
            'records' => $results,
            'count' => count($results),
        ];
    }

    public function insert(): array
    {
        return $this->repository->insert($this->table(), $this->normalizeArrayData($this->request->input('_arraydatos', [])));
    }

    public function update(): array
    {
        return $this->repository->update(
            $this->table(),
            $this->normalizeArrayData($this->request->input('_arraydatos', [])),
            $this->normalizeWhere($this->request->input('_where', []))
        );
    }

    public function delete(): array
    {
        return $this->repository->delete(
            $this->table(),
            $this->normalizeWhere($this->request->input('_where', []))
        );
    }

    public function procedure(): array
    {
        $procedure = (string) $this->request->input('_procedure', '');
        if ($procedure === '') {
            throw new RuntimeException('Debe enviar _procedure');
        }

        $rows = $this->repository->executeProcedure(
            $procedure,
            array_values($this->normalizeArrayData($this->request->input('_arraydatos', [])))
        );

        return $this->formatRows($rows);
    }

    public function insertSelect(): array
    {
        $result = $this->repository->insertSelect(
            table: $this->table(),
            tableSelect: (string) $this->request->input('_tablaSelect', ''),
            data: $this->normalizeInsertSelectData($this->request->input('_arraydatos', [])),
            deleteBefore: $this->normalizeDeleteBefore($this->request->input('_deleteBefore', [])),
            where: $this->normalizeWhere($this->request->input('_where', []))
        );

        return [
            'message' => 'Insercion realizada correctamente',
            'affected' => (int) ($result['affected'] ?? 0),
            'deleted' => (int) ($result['deleted'] ?? 0),
        ];
    }

    public function assignUserProfile(): array
    {
        $payload = $this->request->input('_parametro', []);
        if (!is_array($payload)) {
            throw new RuntimeException('Debe enviar _parametro');
        }

        $profileId = (int) ($payload['perfil'] ?? 0);
        $userId = (int) ($payload['usuario'] ?? 0);

        if ($profileId <= 0 || $userId <= 0) {
            throw new RuntimeException('Debe enviar perfil y usuario válidos');
        }

        $rows = $this->repository->assignUserProfile($profileId, $userId);
        $result = $rows[0] ?? [];
        $code = (int) ($result['_result'] ?? 0);

        if ($code !== 100) {
            throw new RuntimeException((string) ($result['msg'] ?? 'No fue posible asignar el perfil al usuario'));
        }

        return [
            'message' => (string) ($result['msg'] ?? 'Perfil asignado correctamente'),
            'result' => $result,
        ];
    }

    public function boxesByUser(): array
    {
        $userId = (int) $this->request->input('_usuario', 0);
        if ($userId <= 0) {
            throw new RuntimeException('Debe enviar _usuario');
        }

        $rows = $this->repository->listBoxesByUser($userId);
        $boxes = array_map(static function (array $row): array {
            $row['asignada'] = (bool) ($row['asignada'] ?? false);
            return $row;
        }, $rows);

        return [
            'boxes' => $boxes,
            'count' => count($boxes),
        ];
    }

    public function assignBoxesToUser(): array
    {
        $userId = (int) $this->request->input('_idUsuario', 0);
        $boxIds = $this->normalizeIntegerArray($this->request->input('_cajas', []));

        if ($userId <= 0) {
            throw new RuntimeException('Debe enviar _idUsuario');
        }

        $result = $this->repository->replaceUserBoxes($userId, $boxIds);

        return [
            'message' => 'Cajas asignadas correctamente',
            'assignedBoxIds' => $boxIds,
            'inserted' => (int) ($result['inserted'] ?? 0),
            'deleted' => (int) ($result['deleted'] ?? 0),
        ];
    }

    public function searchStockLocations(): array
    {
        $warehouses = $this->decodeWarehouseObjects($this->repository->activeWarehouses());
        $establishments = $this->repository->establishmentsWarehouseAssignments();

        if ((bool) $this->request->input('_principal', false) === true) {
            $locations = $this->mapAssignments(
                warehouses: $warehouses,
                establishments: $establishments,
                column: 'idAuxiliar',
                type: 'PRINCIPAL'
            );

            return ['locations' => $locations, 'count' => count($locations)];
        }

        if ((bool) $this->request->input('_fisicas', false) === true && $this->request->input('_id_principal') === null) {
            $locations = $this->mapAssignments(
                warehouses: $warehouses,
                establishments: $establishments,
                column: 'idBodegaStock',
                type: 'FISICA'
            );

            return ['locations' => $locations, 'count' => count($locations)];
        }

        if ((bool) $this->request->input('_virtual', false) === true) {
            $locations = $this->mapAssignments(
                warehouses: $warehouses,
                establishments: $establishments,
                column: 'idBodegaVitual',
                type: 'VIRTUAL'
            );

            return ['locations' => $locations, 'count' => count($locations)];
        }

        if ($this->request->input('_id_principal') !== null) {
            $principalId = (int) $this->request->input('_id_principal', 0);
            if ($principalId <= 0) {
                throw new RuntimeException('Debe enviar _id_principal');
            }

            $locationIds = [];
            foreach ($establishments as $establishment) {
                if ((int) ($establishment['idAuxiliar'] ?? 0) !== $principalId) {
                    continue;
                }

                if ((bool) $this->request->input('_existencia', false) === true) {
                    $locationIds[] = (int) ($establishment['estockExistencia'] ?? 0);
                } else {
                    $locationIds[] = (int) ($establishment['idBodegaStock'] ?? 0);
                    $locationIds[] = (int) ($establishment['idBodegaVitual'] ?? 0);
                }
            }

            $locationIds = array_values(array_unique(array_filter($locationIds)));
            $locations = array_values(array_filter($warehouses, static fn (array $warehouse) => in_array((int) ($warehouse['id'] ?? 0), $locationIds, true)));

            return ['locations' => $locations, 'count' => count($locations)];
        }

        return [
            'locations' => $warehouses,
            'count' => count($warehouses),
        ];
    }

    private function formatRows(array $rows): array
    {
        $objectColumns = $this->normalizeStringArray($this->request->input('_obj', []));

        if ($objectColumns !== []) {
            foreach ($rows as &$row) {
                foreach ($objectColumns as $column) {
                    if (!array_key_exists($column, $row) || !is_string($row[$column])) {
                        continue;
                    }

                    $decoded = json_decode($row[$column], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $row[$column] = $decoded;
                    }
                }
            }
            unset($row);
        }

        return [
            'records' => $rows,
            'count' => count($rows),
        ];
    }

    private function requireUser(): array
    {
        $context = $this->authContext->resolve($this->request);
        if (($context['success'] ?? false) !== true) {
            throw new RuntimeException((string) ($context['message'] ?? 'Usuario no autenticado'));
        }

        return $context;
    }

    private function table(): string
    {
        $table = (string) $this->request->input('_tabla', '');
        if ($table === '') {
            throw new RuntimeException('Debe enviar _tabla');
        }

        return $table;
    }

    private function columns(): array
    {
        $columns = $this->request->input('_columnas', ['*']);
        if (!is_array($columns) || $columns === []) {
            return ['*'];
        }

        return array_map(fn (mixed $column) => (string) $column, $columns);
    }

    private function normalizeWhere(mixed $where): array
    {
        return is_array($where) ? $where : [];
    }

    private function normalizeOrderBy(mixed $orderBy): array
    {
        return is_array($orderBy) ? $orderBy : [];
    }

    private function normalizeLimit(mixed $limit): ?int
    {
        if (is_array($limit)) {
            return null;
        }

        if ($limit === null || $limit === '') {
            return null;
        }

        $value = (int) $limit;
        return $value > 0 ? $value : null;
    }

    private function normalizeArrayData(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $context = null;
        $normalized = [];

        foreach ($payload as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $normalized[$key] = $this->normalizeValue($value, $context);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value, ?array &$context): mixed
    {
        if ($value === 'USUARIO_LOGUEADO') {
            $context ??= $this->requireUser();
            return (int) ($context['full_user']['ID'] ?? 0);
        }

        if (is_array($value) && count($value) === 2 && ($value[1] ?? null) === 'tabla') {
            return $value[0] ?? null;
        }

        if (is_array($value) && count($value) === 2) {
            return $value[0] ?? null;
        }

        return $value;
    }

    private function normalizeStringArray(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value) => is_string($value) ? $value : '',
            $payload
        )));
    }

    private function normalizeDeleteBefore(mixed $payload): array
    {
        return is_array($payload) ? $payload : [];
    }

    private function normalizeIntegerArray(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $value) => (int) $value,
            $payload
        )));
    }

    private function normalizeInsertSelectData(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $context = null;
        $normalized = [];

        foreach ($payload as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if (is_array($value) && count($value) === 2 && ($value[1] ?? null) === 'tabla') {
                $normalized[$key] = [
                    'mode' => 'column',
                    'value' => (string) ($value[0] ?? ''),
                ];
                continue;
            }

            $normalized[$key] = [
                'mode' => 'value',
                'value' => $this->normalizeValue($value, $context),
            ];
        }

        return $normalized;
    }

    private function decodeWarehouseObjects(array $rows): array
    {
        return array_map(static function (array $row): array {
            if (isset($row['obj']) && is_string($row['obj'])) {
                $decoded = json_decode($row['obj'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $row = array_merge($row, $decoded);
                }
            }

            return $row;
        }, $rows);
    }

    private function mapAssignments(array $warehouses, array $establishments, string $column, string $type): array
    {
        return array_map(static function (array $warehouse) use ($establishments, $column, $type): array {
            $warehouseId = (int) ($warehouse['id'] ?? 0);
            $warehouse['asignado'] = 'NO';
            $warehouse['idEsta'] = 0;
            $warehouse['nombreEsta'] = '';
            $warehouse['tipLocacion'] = $type;

            foreach ($establishments as $establishment) {
                if ((int) ($establishment[$column] ?? 0) !== $warehouseId) {
                    continue;
                }

                $warehouse['asignado'] = 'SI';
                $warehouse['idEsta'] = (int) ($establishment['id'] ?? 0);
                $warehouse['nombreEsta'] = (string) ($establishment['nombre'] ?? '');
                break;
            }

            return $warehouse;
        }, $warehouses);
    }
}
