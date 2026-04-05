<?php
/**
 * Test Runner - Ejecuta todos los tests del proyecto
 */

$projectRoot = __DIR__ . '/..';
require_once $projectRoot . '/vendor/autoload.php';

echo "\n";
echo "╔" . str_repeat("═", 78) . "╗\n";
echo "║" . str_pad("TEST SUITE - JDS Backend 2026", 78) . "║\n";
echo "╚" . str_repeat("═", 78) . "╝\n\n";

$testSuites = [
    'Unit Tests' => __DIR__ . '/Unit',
    'Integration Tests' => __DIR__ . '/Integration',
];

$results = [];
$totalPassed = 0;
$totalFailed = 0;

foreach ($testSuites as $suiteName => $suitePath) {
    echo "📂 $suiteName\n";
    echo str_repeat("─", 80) . "\n";
    
    if (!is_dir($suitePath)) {
        echo "  ⚠️  Suite directory not found: $suitePath\n\n";
        continue;
    }

    $testFiles = glob($suitePath . '/*Test.php');
    
    if (empty($testFiles)) {
        echo "  ℹ️  No tests found in $suitePath\n\n";
        continue;
    }

    foreach ($testFiles as $testFile) {
        $testName = basename($testFile, '.php');
        echo "  ▶ Running $testName... ";

        [$exitCode, $output] = runPhpTestFile($testFile);
        
        if ($exitCode === 0) {
            echo "✓ PASSED\n";
            $totalPassed++;
            $results[$suiteName][$testName] = 'PASSED';
        } else {
            echo "✗ FAILED\n";
            echo "─" . str_repeat("─", 79) . "\n";
            echo $output;
            echo "─" . str_repeat("─", 79) . "\n";
            $totalFailed++;
            $results[$suiteName][$testName] = 'FAILED';
        }
    }
    
    echo "\n";
}

// Summary
echo "╔" . str_repeat("═", 78) . "╗\n";
echo "║" . str_pad("SUMMARY", 78) . "║\n";
echo "╠" . str_repeat("═", 78) . "╣\n";

foreach ($results as $suiteName => $tests) {
    echo "║ $suiteName:\n";
    foreach ($tests as $testName => $status) {
        $icon = $status === 'PASSED' ? '✓' : '✗';
        $padding = 73 - strlen($testName) - strlen($status);
        echo "║   $icon $testName" . str_repeat(".", $padding) . "$status\n";
    }
}

echo "╠" . str_repeat("═", 78) . "╣\n";
echo "║ " . str_pad("Total Passed: $totalPassed", 77) . "║\n";
echo "║ " . str_pad("Total Failed: $totalFailed", 77) . "║\n";
echo "╚" . str_repeat("═", 78) . "╝\n\n";

// Exit with appropriate code
exit($totalFailed > 0 ? 1 : 0);

function runPhpTestFile(string $testFile): array
{
    $command = 'php ' . escapeshellarg($testFile);
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes);

    if (!is_resource($process)) {
        return [1, "No fue posible ejecutar el proceso para $testFile"];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return [$exitCode, $stdout . $stderr];
}
