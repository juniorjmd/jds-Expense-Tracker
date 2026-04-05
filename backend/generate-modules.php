<?php
declare(strict_types=1);

if ($argc < 2) {
    echo "Uso: php generate-module.php <NombreModulo>\n";
    exit(1);
}

$moduleName = ucfirst($argv[1]);
$moduleLower = strtolower($argv[1]);

$basePath = __DIR__ . "/app/Modules/{$moduleName}";

$folders = [
    $basePath,
    "{$basePath}/Services",
    "{$basePath}/Repositories",
    "{$basePath}/Actions",
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
        echo "Carpeta creada: {$folder}\n";
    }
}

$files = [
    "{$basePath}/{$moduleName}Controller.php" => <<<PHP
<?php
declare(strict_types=1);

namespace App\\Modules\\{$moduleName};

use App\\Core\\Http\\Request;
use App\\Modules\\{$moduleName}\\Services\\{$moduleName}Service;

final class {$moduleName}Controller
{
    public function __construct(
        private ?{$moduleName}Service \$service = null
    ) {
        \$this->service ??= new {$moduleName}Service();
    }

    public function index(Request \$request): array
    {
        return [
            'module' => '{$moduleLower}',
            'message' => '{$moduleName} module activo'
        ];
    }
}

PHP,
    "{$basePath}/Services/{$moduleName}Service.php" => <<<PHP
<?php
declare(strict_types=1);

namespace App\\Modules\\{$moduleName}\\Services;

use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}Repository;

final class {$moduleName}Service
{
    public function __construct(
        private ?{$moduleName}Repository \$repository = null
    ) {
        \$this->repository ??= new {$moduleName}Repository();
    }
}

PHP,
    "{$basePath}/Repositories/{$moduleName}Repository.php" => <<<PHP
<?php
declare(strict_types=1);

namespace App\\Modules\\{$moduleName}\\Repositories;

final class {$moduleName}Repository
{
}

PHP,
];

foreach ($files as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        echo "Archivo creado: {$file}\n";
    } else {
        echo "El archivo {$file} ya existe. No se sobrescribirá.\n";
    }
}

echo "Módulo {$moduleName} generado correctamente.\n";