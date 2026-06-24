<?php
/**
 * Pruebas Unitarias — Validación del modelo Cancion
 *
 * Valida la lógica de Cancion::validarDatos() de forma aislada,
 * sin conexión real a la base de datos ni archivos físicos.
 *
 * Cómo ejecutar:
 *   php tests/unitarias/TestValidacionCancion.php
 */

require_once __DIR__ . '/../../src/modelo/Cancion.php';

// ─────────────────────────────────────────────
// Mini-framework de pruebas (mismo patrón que TestValidacionUsuario)
// ─────────────────────────────────────────────

$resultados = ['pasados' => 0, 'fallados' => 0];

function assert_igual(string $descripcion, $esperado, $obtenido): void {
    global $resultados;
    if ($esperado === $obtenido) {
        echo "[PASADO] {$descripcion}\n";
        $resultados['pasados']++;
    } else {
        $esperadoStr = var_export($esperado, true);
        $obtenidoStr = var_export($obtenido, true);
        echo "[FALLADO] {$descripcion}\n";
        echo "          Esperado: {$esperadoStr}\n";
        echo "          Obtenido: {$obtenidoStr}\n";
        $resultados['fallados']++;
    }
}

function assert_es_error(string $descripcion, $obtenido): void {
    global $resultados;
    if ($obtenido !== true) {
        echo "[PASADO] {$descripcion}\n";
        $resultados['pasados']++;
    } else {
        echo "[FALLADO] {$descripcion} — Se esperaba un mensaje de error pero se obtuvo true\n";
        $resultados['fallados']++;
    }
}

// ─────────────────────────────────────────────
// Stub de conexión PDO (sin base de datos real)
// ─────────────────────────────────────────────

class ConexionSimuladaCancion {
    public function prepare($sql) { return null; }
}

// Instanciar el modelo con conexión simulada
$cancion = new Cancion(new ConexionSimuladaCancion());

// ─────────────────────────────────────────────
// BLOQUE A — Validación de campos obligatorios
// ─────────────────────────────────────────────

echo "\n=== A. Validación de campos obligatorios en Cancion ===\n";

// Caso 1: título vacío
$resultado = $cancion->validarDatos('', 'uploads/canciones/cancion.mp3', 'Hip-Hop');
assert_es_error('Título vacío debe devolver error', $resultado);

// Caso 2: ruta de archivo vacía (no se ha subido el MP3)
$resultado = $cancion->validarDatos('Mi Canción', '', 'Hip-Hop');
assert_es_error('Ruta de archivo vacía debe devolver error', $resultado);

// Caso 3: género vacío
$resultado = $cancion->validarDatos('Mi Canción', 'uploads/canciones/cancion.mp3', '');
assert_es_error('Género vacío debe devolver error', $resultado);

// Caso 4: los tres campos vacíos a la vez
$resultado = $cancion->validarDatos('', '', '');
assert_es_error('Todos los campos vacíos deben devolver error', $resultado);

// Caso 5: título solo con espacios en blanco (trim debe detectarlo como vacío)
$resultado = $cancion->validarDatos('   ', 'uploads/canciones/cancion.mp3', 'Trap');
assert_es_error('Título con solo espacios debe devolver error', $resultado);

// Caso 6: género solo con espacios en blanco
$resultado = $cancion->validarDatos('Mi Canción', 'uploads/canciones/cancion.mp3', '   ');
assert_es_error('Género con solo espacios debe devolver error', $resultado);

// ─────────────────────────────────────────────
// BLOQUE B — Validaciones que deben pasar (datos correctos)
// ─────────────────────────────────────────────

echo "\n=== B. Datos válidos en Cancion ===\n";

// Caso 7: todos los campos correctamente rellenados
$resultado = $cancion->validarDatos('Mi Canción', 'uploads/canciones/abc123.mp3', 'Hip-Hop');
assert_igual(
    'Todos los campos válidos deben devolver true',
    true,
    $resultado
);

// Caso 8: géneros del sistema (Trap)
$resultado = $cancion->validarDatos('Flow Urbano', 'uploads/canciones/xyz789.mp3', 'Trap');
assert_igual('Género "Trap" con datos válidos debe devolver true', true, $resultado);

// Caso 9: título con caracteres especiales y tildes
$resultado = $cancion->validarDatos('Éxito Número Uno', 'uploads/canciones/exito.mp3', 'R&B');
assert_igual('Título con tildes y caracteres especiales debe pasar', true, $resultado);

// ─────────────────────────────────────────────
// BLOQUE C — Comprobación del formato de duración por defecto
// ─────────────────────────────────────────────

echo "\n=== C. Formato de duración por defecto ===\n";

// Caso 10: la duración por defecto definida en el modelo es '00:03:00'
// Esta constante es la que se usará si no se calcula la duración real del MP3
$duracionPorDefecto = '00:03:00';
$formatoCorrecto = (bool) preg_match('/^\d{2}:\d{2}:\d{2}$/', $duracionPorDefecto);
assert_igual(
    'La duración por defecto tiene formato HH:MM:SS válido',
    true,
    $formatoCorrecto
);

// Caso 11: una duración real calculada también debe cumplir el formato
$duracionReal = '00:04:32';
$formatoReal = (bool) preg_match('/^\d{2}:\d{2}:\d{2}$/', $duracionReal);
assert_igual(
    'Una duración calculada (00:04:32) tiene formato HH:MM:SS válido',
    true,
    $formatoReal
);

// Caso 12: una duración con formato incorrecto debe fallar la expresión regular
$duracionMalFormada = '4:32';
$formatoMalo = (bool) preg_match('/^\d{2}:\d{2}:\d{2}$/', $duracionMalFormada);
assert_igual(
    'Duración "4:32" (sin horas) no cumple el formato HH:MM:SS',
    false,
    $formatoMalo
);

// ─────────────────────────────────────────────
// BLOQUE D — Comprobación del tipo de archivo permitido
// ─────────────────────────────────────────────

echo "\n=== D. Validación de extensión de archivo de canción ===\n";

/**
 * Función auxiliar que replica la validación de extensión
 * del controlador CancionController::subirCancion().
 *
 * @param string $nombreArchivo Nombre del archivo subido
 * @return bool true si la extensión es .mp3, false en caso contrario
 */
function esArchivoMp3(string $nombreArchivo): bool {
    // strtolower evita que .MP3 o .Mp3 pasen desapercibidos
    return strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION)) === 'mp3';
}

// Caso 13: archivo con extensión .mp3 en minúsculas (válido)
assert_igual('Archivo "cancion.mp3" debe ser aceptado', true, esArchivoMp3('cancion.mp3'));

// Caso 14: archivo con extensión .MP3 en mayúsculas (debe normalizarse y aceptarse)
assert_igual('Archivo "CANCION.MP3" debe ser aceptado (strtolower)', true, esArchivoMp3('CANCION.MP3'));

// Caso 15: archivo con extensión .wav (no permitido)
assert_igual('Archivo "cancion.wav" no debe ser aceptado', false, esArchivoMp3('cancion.wav'));

// Caso 16: archivo con extensión .ogg (no permitido)
assert_igual('Archivo "cancion.ogg" no debe ser aceptado', false, esArchivoMp3('cancion.ogg'));

// Caso 17: archivo con extensión .php (intento de subida maliciosa)
assert_igual('Archivo "malicioso.php" no debe ser aceptado', false, esArchivoMp3('malicioso.php'));

// ─────────────────────────────────────────────
// Resumen final
// ─────────────────────────────────────────────

$total = $resultados['pasados'] + $resultados['fallados'];
echo "\n========================================\n";
echo "RESUMEN: {$resultados['pasados']} pasados / {$resultados['fallados']} fallados / {$total} total\n";
echo "========================================\n";

exit($resultados['fallados'] > 0 ? 1 : 0);
