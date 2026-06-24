<?php
/**
 * Runner principal de pruebas de SoundPlay
 *
 * Ejecuta en secuencia todas las pruebas unitarias y de integración,
 * muestra un resumen global y devuelve código de salida 1 si alguna falla.
 *
 * Cómo ejecutar (desde la raíz del proyecto):
 *   php tests/ejecutar_todos.php
 *
 * Para ejecutar solo un bloque:
 *   php tests/unitarias/TestValidacionUsuario.php
 *   php tests/unitarias/TestValidacionCancion.php
 *   php tests/integracion/TestConexionBD.php
 */

// ─────────────────────────────────────────────
// Definición de los archivos de prueba
// ─────────────────────────────────────────────

$suites = [
    'Unitarias — Validación' => [
        __DIR__ . '/unitarias/TestValidacionUsuario.php',
        __DIR__ . '/unitarias/TestValidacionCancion.php',
    ],
    'Unitarias — Funcionalidad' => [
        __DIR__ . '/unitarias/TestUsuario.php',
        __DIR__ . '/unitarias/TestCancionAlbum.php',
    ],
    'Integración' => [
        __DIR__ . '/integracion/TestConexionBD.php',
        __DIR__ . '/integracion/TestFlujosBD.php',
    ],
];

// ─────────────────────────────────────────────
// Ejecución de cada suite en un proceso separado
// ─────────────────────────────────────────────

$fallasGlobales = 0;
$pasadosGlobales = 0;

echo "╔══════════════════════════════════════════╗\n";
echo "║      RUNNER DE PRUEBAS — SOUNDPLAY       ║\n";
echo "╚══════════════════════════════════════════╝\n";

foreach ($suites as $nombreSuite => $archivos) {
    echo "\n┌─ Suite: {$nombreSuite} ─────────────────────────\n";

    foreach ($archivos as $archivo) {
        $nombreArchivo = basename($archivo);
        echo "│\n│ >> Ejecutando: {$nombreArchivo}\n│\n";

        // Capturar la salida del script de prueba para mostrarla indentada
        ob_start();
        // Incluir el archivo en su propio contexto (variables independientes por bloque)
        // Se usa un proceso separado para evitar que una prueba afecte a las demás
        $salida = shell_exec('php ' . escapeshellarg($archivo) . ' 2>&1');
        ob_end_clean();

        // Indentar cada línea de salida para legibilidad
        $lineas = explode("\n", trim($salida));
        foreach ($lineas as $linea) {
            echo "│   {$linea}\n";
        }

        // Contar resultados a partir de la línea RESUMEN
        if (preg_match('/RESUMEN: (\d+) pasados \/ (\d+) fallados/', $salida, $coincidencias)) {
            $pasadosGlobales += (int) $coincidencias[1];
            $fallasGlobales  += (int) $coincidencias[2];
        }
    }

    echo "└───────────────────────────────────────────\n";
}

// ─────────────────────────────────────────────
// Resumen global
// ─────────────────────────────────────────────

$totalGlobal = $pasadosGlobales + $fallasGlobales;
$estado = $fallasGlobales === 0 ? 'OK — Todas las pruebas pasaron' : 'ATENCIÓN — Hay pruebas fallidas';

echo "\n╔══════════════════════════════════════════╗\n";
echo "║  RESULTADO GLOBAL                        ║\n";
echo "╠══════════════════════════════════════════╣\n";
printf("║  Pasadas:  %-30d║\n", $pasadosGlobales);
printf("║  Falladas: %-30d║\n", $fallasGlobales);
printf("║  Total:    %-30d║\n", $totalGlobal);
echo "╠══════════════════════════════════════════╣\n";
printf("║  Estado:   %-30s║\n", $estado);
echo "╚══════════════════════════════════════════╝\n\n";

// Código de salida útil para pipelines de integración continua
exit($fallasGlobales > 0 ? 1 : 0);
