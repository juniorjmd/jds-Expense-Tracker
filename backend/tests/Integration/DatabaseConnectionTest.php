<?php
/**
 * Test de conexión a BD MySQL
 * Verifica que las credenciales son correctas
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database\Connection;

// Cargar archivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

echo "=== TEST DE CONEXIÓN A BD ===\n";

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? '';

try {
    $pdo = Connection::get();
    echo "✓ Conexión exitosa a la BD\n";
    
    // Verificar que la BD tiene datos básicos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "✓ Tabla 'usuarios' accesible\n";
    echo "  Total de usuarios: " . $result['total'] . "\n";
    
    // Verificar vistas
    $views = ['vw_session', 'vw_usuario', 'vw_perfil_recurso', 'vw_usuario_response_ok'];
    foreach ($views as $view) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $view LIMIT 1");
            echo "✓ Vista '$view' accesible\n";
        } catch (Exception $e) {
            echo "✗ Vista '$view' NO accesible: " . $e->getMessage() . "\n";
        }
    }
    
    // Verificar procedimiento almacenado
    try {
        // Esto solo verifica que el procedimiento existe
        $stmt = $pdo->query(
            "SELECT ROUTINE_NAME FROM information_schema.ROUTINES " .
            "WHERE ROUTINE_SCHEMA = " . $pdo->quote($database) . " AND ROUTINE_NAME = 'sp_login'"
        );
        if ($stmt->rowCount() > 0) {
            echo "✓ Procedimiento 'sp_login' existe\n";
        } else {
            echo "✗ Procedimiento 'sp_login' NO encontrado\n";
        }
    } catch (Exception $e) {
        echo "✗ Error verificando procedimiento: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== RESULTADO: BD LISTA PARA TESTS ===\n";
    
} catch (Exception $e) {
    echo "✗ Error de conexión:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "\n⚠️  VERIFIQUE:\n";
    echo "  - Host: {$host}\n";
    echo "  - Port: {$port}\n";
    echo "  - Base de datos: {$database}\n";
    exit(1);
}
