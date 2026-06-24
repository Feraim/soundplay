<?php
/**
 * Prueba de Integración — Conexión a la base de datos
 *
 * Comprueba que la clase Database puede establecer una conexión PDO real
 * con MariaDB y que las configuraciones de seguridad están activas.
 *
 * REQUISITO: XAMPP debe estar en ejecución con la base de datos 'soundplay' importada.
 *
 * Cómo ejecutar (desde la raíz del proyecto):
 *   php tests/integracion/TestConexionBD.php
 */

// La clase Database necesita que la ruta base sea la raíz del proyecto
chdir(__DIR__ . '/../../');
require_once 'config/conexion.php';

// ─────────────────────────────────────────────
// Mini-framework de pruebas
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

function assert_no_es_null(string $descripcion, $valor): void {
    global $resultados;
    if ($valor !== null && $valor !== false) {
        echo "[PASADO] {$descripcion}\n";
        $resultados['pasados']++;
    } else {
        echo "[FALLADO] {$descripcion} — El valor es null o false\n";
        $resultados['fallados']++;
    }
}

// ─────────────────────────────────────────────
// BLOQUE A — Comprobación de la conexión PDO
// ─────────────────────────────────────────────

echo "\n=== A. Conexión a la base de datos (integración) ===\n";
echo "NOTA: Requiere que XAMPP esté en ejecución con la BD 'soundplay' importada.\n\n";

$pdo = null;

try {
    $db = new Database();
    $pdo = $db->conectar();

    // Caso 1: el objeto devuelto debe ser una instancia de PDO
    assert_igual(
        'Database::conectar() debe devolver una instancia de PDO',
        true,
        ($pdo instanceof PDO)
    );

    // Caso 2: el modo de error debe ser ERRMODE_EXCEPTION
    $modoError = $pdo->getAttribute(PDO::ATTR_ERRMODE);
    assert_igual(
        'El modo de error PDO debe ser ERRMODE_EXCEPTION',
        PDO::ERRMODE_EXCEPTION,
        $modoError
    );

    // Caso 3: el modo de fetch por defecto debe ser FETCH_ASSOC
    $modoFetch = $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
    assert_igual(
        'El modo de fetch por defecto debe ser FETCH_ASSOC',
        PDO::FETCH_ASSOC,
        $modoFetch
    );

} catch (Exception $e) {
    // Si la conexión falla, registrar el error y marcar todas las pruebas como falladas
    echo "[ERROR] No se pudo conectar a la base de datos: " . $e->getMessage() . "\n";
    echo "        Asegúrate de que XAMPP está en ejecución y la BD 'soundplay' existe.\n";
    $resultados['fallados'] += 3;
}

// ─────────────────────────────────────────────
// BLOQUE B — Comprobación de la existencia de tablas
// ─────────────────────────────────────────────

echo "\n=== B. Existencia de tablas en la base de datos ===\n";

if ($pdo !== null) {
    // Tablas que deben existir según el esquema de soundplay.sql
    $tablaEsperadas = ['usuarios', 'artistas', 'albumes', 'canciones', 'transacciones'];

    foreach ($tablaEsperadas as $tabla) {
        try {
            // Consulta de comprobación: si la tabla no existe lanzará una excepción
            $stmt = $pdo->query("SELECT 1 FROM {$tabla} LIMIT 1");
            assert_igual("La tabla '{$tabla}' existe y es accesible", true, true);
        } catch (PDOException $e) {
            echo "[FALLADO] La tabla '{$tabla}' no existe o no es accesible\n";
            echo "          Error: " . $e->getMessage() . "\n";
            $resultados['fallados']++;
        }
    }
}

// ─────────────────────────────────────────────
// BLOQUE C — Comprobación de sentencias preparadas
// ─────────────────────────────────────────────

echo "\n=== C. Sentencias preparadas (prevención de SQL Injection) ===\n";

if ($pdo !== null) {
    try {
        // Caso: una sentencia preparada debe ejecutarse sin errores con un parámetro externo
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        // Simulamos un intento de inyección SQL; las sentencias preparadas deben neutralizarlo
        $emailMalicioso = "' OR '1'='1";
        $stmt->bindParam(':email', $emailMalicioso);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Un email de inyección no debe devolver ningún registro real
        assert_igual(
            'La inyección SQL mediante email malicioso no debe devolver registros',
            false,   // Esperamos false (ningún resultado)
            $resultado
        );

    } catch (PDOException $e) {
        echo "[FALLADO] Error al ejecutar la sentencia preparada: " . $e->getMessage() . "\n";
        $resultados['fallados']++;
    }
}

// ─────────────────────────────────────────────
// Resumen final
// ─────────────────────────────────────────────

$total = $resultados['pasados'] + $resultados['fallados'];
echo "\n========================================\n";
echo "RESUMEN: {$resultados['pasados']} pasados / {$resultados['fallados']} fallados / {$total} total\n";
echo "========================================\n";

exit($resultados['fallados'] > 0 ? 1 : 0);
