<?php
/**
 * Pruebas de Integración — Flujos contra la base de datos real
 *
 * Prueba que varios componentes trabajen juntos correctamente:
 *   - Usuario: registrar + obtenerPorEmail + password_verify + banear
 *   - Album: crearAlbum + obtenerAlbumesPorArtista
 *   - Cancion: guardarCancion + buscar + obtenerCancionesPorGenero
 *   - Flujo completo: registro → login → baneo → login bloqueado
 *
 * REQUISITO: XAMPP en ejecución con la BD 'soundplay' importada.
 * Todos los datos de prueba se eliminan al finalizar (limpieza automática).
 *
 * Cómo ejecutar (desde la raíz del proyecto):
 *   php tests/integracion/TestFlujosBD.php
 */

chdir(__DIR__ . '/../../');
require_once 'config/conexion.php';
require_once 'src/modelo/Usuario.php';
require_once 'src/modelo/Album.php';
require_once 'src/modelo/Cancion.php';

// ─────────────────────────────────────────────
// Mini-framework de pruebas
// ─────────────────────────────────────────────

$resultados = ['pasados' => 0, 'fallados' => 0];

function assert_igual(string $desc, $esperado, $obtenido): void {
    global $resultados;
    if ($esperado === $obtenido) {
        echo "[PASADO] {$desc}\n";
        $resultados['pasados']++;
    } else {
        echo "[FALLADO] {$desc}\n";
        echo "          Esperado: " . var_export($esperado, true) . "\n";
        echo "          Obtenido: " . var_export($obtenido, true) . "\n";
        $resultados['fallados']++;
    }
}

// ─────────────────────────────────────────────
// Conexión real + datos de prueba
// ─────────────────────────────────────────────

$pdo = null;
try {
    $db  = new Database();
    $pdo = $db->conectar();
    echo "Conexión a BD establecida.\n";
} catch (Exception $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    echo "        Asegúrate de que XAMPP está en ejecución y la BD 'soundplay' existe.\n";
    exit(1);
}

// Email de prueba único para no colisionar con datos reales
$emailPrueba = 'test_integracion_' . time() . '@soundplay.test';
$clavePrueba = 'ClaveTest_9988';

// IDs a limpiar al final
$idUsuarioPrueba  = null;
$idAlbumPrueba    = null;
$idCancionPrueba  = null;

// ─────────────────────────────────────────────
// BLOQUE A — Flujo de registro: registrar() → obtenerPorEmail()
// ─────────────────────────────────────────────

echo "\n=== A. Flujo de registro de usuario ===\n";

$usuarioModel = new Usuario($pdo);

// Caso 1: registrar() inserta el usuario en la BD real y devuelve true
$resultadoRegistro = $usuarioModel->registrar($emailPrueba, $clavePrueba, 'user', 1);
assert_igual(
    'registrar() debe devolver true al insertar un usuario nuevo en la BD',
    true,
    $resultadoRegistro
);

// Caso 2: obtenerPorEmail() encuentra el usuario recién creado
$usuarioObtenido = $usuarioModel->obtenerPorEmail($emailPrueba);
assert_igual(
    'obtenerPorEmail() debe encontrar el usuario recién registrado',
    true,
    ($usuarioObtenido !== false && is_array($usuarioObtenido))
);

if ($usuarioObtenido) {
    // Guardar ID para la limpieza final
    $idUsuarioPrueba = $usuarioObtenido['id_usuario'];

    // Caso 3: el email almacenado coincide
    assert_igual(
        'El email almacenado debe coincidir con el email de registro',
        $emailPrueba,
        $usuarioObtenido['email']
    );

    // Caso 4: la contraseña guardada es un hash bcrypt (nunca el texto plano)
    assert_igual(
        'La contraseña almacenada no debe ser el texto plano original',
        false,
        ($usuarioObtenido['contrasena'] === $clavePrueba)
    );

    // Caso 5: password_verify confirma que el hash pertenece a la contraseña original
    assert_igual(
        'password_verify debe validar la contraseña correcta contra el hash almacenado',
        true,
        password_verify($clavePrueba, $usuarioObtenido['contrasena'])
    );

    // Caso 6: password_verify rechaza una contraseña incorrecta
    assert_igual(
        'password_verify debe rechazar una contraseña incorrecta',
        false,
        password_verify('ClaveEquivocada_000', $usuarioObtenido['contrasena'])
    );
}

// Caso 7: registrar() con el mismo email devuelve false (violación de UNIQUE en BD)
$duplicado = $usuarioModel->registrar($emailPrueba, $clavePrueba, 'user', 1);
assert_igual(
    'registrar() con email duplicado debe devolver false (restricción UNIQUE de la BD)',
    false,
    $duplicado
);

// ─────────────────────────────────────────────
// BLOQUE B — Flujo de baneo: banearUsuario() → verificar banned → login bloqueado
// ─────────────────────────────────────────────

echo "\n=== B. Flujo de baneo de usuario ===\n";

if ($idUsuarioPrueba !== null) {
    // Caso 8: banearUsuario() actualiza la columna banned = 1 en BD
    $baneado = $usuarioModel->banearUsuario($emailPrueba, 1);
    assert_igual(
        'banearUsuario() debe devolver true al actualizar el campo banned',
        true,
        $baneado
    );

    // Caso 9: obtenerPorEmail refleja banned = 1 en BD (múltiples sistemas cooperan)
    $usuarioBaneado = $usuarioModel->obtenerPorEmail($emailPrueba);
    assert_igual(
        'El campo banned debe ser 1 en la BD tras el baneo',
        1,
        (int)($usuarioBaneado['banned'] ?? -1)
    );

    // Caso 10: el flujo de login detecta banned = 1 y lo rechaza (lógica del controlador)
    //          Replicamos la comprobación del UsuarioController::procesarLogin()
    $usuario       = $usuarioModel->obtenerPorEmail($emailPrueba);
    $contrasenaOk  = $usuario && password_verify($clavePrueba, $usuario['contrasena']);
    $estaBaneado   = $contrasenaOk && isset($usuario['banned']) && (int)$usuario['banned'] === 1;
    assert_igual(
        'La lógica del login debe detectar que la cuenta está baneada aunque la contraseña sea correcta',
        true,
        $estaBaneado
    );

    // Caso 11: desbanear → banned vuelve a 0
    $usuarioModel->banearUsuario($emailPrueba, 0);
    $usuarioDesbaneado = $usuarioModel->obtenerPorEmail($emailPrueba);
    assert_igual(
        'Tras el desbaneo, el campo banned debe ser 0',
        0,
        (int)($usuarioDesbaneado['banned'] ?? -1)
    );
}

// ─────────────────────────────────────────────
// BLOQUE C — Flujo de álbum y canción
// ─────────────────────────────────────────────

echo "\n=== C. Flujo de creación de álbum y canción ===\n";

// Necesitamos un artista válido en la tabla artistas para satisfacer la FK.
// Usamos el primer artista existente en la BD de prueba (si hay alguno).
$idArtistaExistente = null;
try {
    $stmt = $pdo->query("SELECT id_artista FROM artistas LIMIT 1");
    $artista = $stmt->fetch(PDO::FETCH_ASSOC);
    $idArtistaExistente = $artista ? (int)$artista['id_artista'] : null;
} catch (PDOException $e) {
    // La tabla artistas podría no existir aún
}

if ($idArtistaExistente !== null) {
    $albumModel  = new Album($pdo);
    $cancionModel = new Cancion($pdo);

    // Caso 12: crearAlbum() inserta el álbum en la BD real
    $resultadoAlbum = $albumModel->crearAlbum(
        $idArtistaExistente,
        'Álbum de Prueba Integración',
        'uploads/portadas/test_integracion.png'
    );
    assert_igual(
        'crearAlbum() debe devolver true al insertar el álbum en la BD',
        true,
        $resultadoAlbum
    );

    // Obtener el ID del álbum recién creado para las siguientes pruebas y la limpieza
    try {
        $stmt = $pdo->prepare(
            "SELECT id_album FROM albumes WHERE id_artista = :id AND titulo = :titulo ORDER BY id_album DESC LIMIT 1"
        );
        $stmt->bindParam(':id',    $idArtistaExistente, PDO::PARAM_INT);
        $tituloAlbum = 'Álbum de Prueba Integración';
        $stmt->bindParam(':titulo', $tituloAlbum);
        $stmt->execute();
        $albumRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $idAlbumPrueba = $albumRow ? (int)$albumRow['id_album'] : null;
    } catch (PDOException $e) { /* silencioso */ }

    // Caso 13: obtenerAlbumesPorArtista() incluye el álbum recién creado
    $albumesArtista = $albumModel->obtenerAlbumesPorArtista($idArtistaExistente);
    $encontrado = false;
    foreach ($albumesArtista as $a) {
        if ($a['titulo'] === 'Álbum de Prueba Integración') {
            $encontrado = true;
            break;
        }
    }
    assert_igual(
        'obtenerAlbumesPorArtista() debe incluir el álbum recién creado en la lista',
        true,
        $encontrado
    );

    if ($idAlbumPrueba !== null) {
        // Caso 14: guardarCancion() inserta la canción vinculada al álbum creado
        $resultadoCancion = $cancionModel->guardarCancion(
            $idAlbumPrueba,
            'Canción de Prueba Integración',
            'uploads/canciones/test_integracion.mp3',
            'Rock',
            '00:03:00'
        );
        assert_igual(
            'guardarCancion() debe devolver true al insertar la canción en la BD',
            true,
            $resultadoCancion
        );

        // Obtener ID de la canción para la limpieza
        try {
            $stmt = $pdo->prepare(
                "SELECT id_cancion FROM canciones WHERE id_album = :id AND titulo = :titulo ORDER BY id_cancion DESC LIMIT 1"
            );
            $stmt->bindParam(':id', $idAlbumPrueba, PDO::PARAM_INT);
            $tituloCancion = 'Canción de Prueba Integración';
            $stmt->bindParam(':titulo', $tituloCancion);
            $stmt->execute();
            $cancionRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $idCancionPrueba = $cancionRow ? (int)$cancionRow['id_cancion'] : null;
        } catch (PDOException $e) { /* silencioso */ }

        // Caso 15: buscar() encuentra la canción recién insertada por título
        $resultadosBusqueda = $cancionModel->buscar('Canción de Prueba Integración');
        $encontradaEnBusqueda = false;
        foreach ($resultadosBusqueda as $c) {
            if ($c['titulo'] === 'Canción de Prueba Integración') {
                $encontradaEnBusqueda = true;
                break;
            }
        }
        assert_igual(
            'buscar() debe encontrar la canción recién insertada por su título exacto',
            true,
            $encontradaEnBusqueda
        );

        // Caso 16: obtenerCancionesPorGenero() lista la canción en el género correcto
        $cancionesPorGenero = $cancionModel->obtenerCancionesPorGenero('Rock');
        $encontradaEnGenero = false;
        foreach ($cancionesPorGenero as $c) {
            if ($c['titulo'] === 'Canción de Prueba Integración') {
                $encontradaEnGenero = true;
                break;
            }
        }
        assert_igual(
            'obtenerCancionesPorGenero("Rock") debe incluir la canción recién creada',
            true,
            $encontradaEnGenero
        );
    }
} else {
    echo "[OMITIDO] No hay artistas en la BD, se omiten pruebas de álbum y canción.\n";
    echo "          Importa soundplay.sql con datos de prueba para ejecutar este bloque.\n";
}

// ─────────────────────────────────────────────
// BLOQUE D — Búsqueda sin resultados
// ─────────────────────────────────────────────

echo "\n=== D. Búsqueda sin resultados ===\n";

$cancionModelBusq = new Cancion($pdo);

// Caso 17: término inexistente → array vacío (nunca false)
$sinResultados = $cancionModelBusq->buscar('xxxxxxxxxTerminoQueNoExisteEnNingunaCancion');
assert_igual(
    'buscar() con término inexistente debe devolver array vacío',
    0,
    count($sinResultados)
);
assert_igual(
    'buscar() nunca debe devolver false ni null, siempre un array',
    true,
    is_array($sinResultados)
);

// ─────────────────────────────────────────────
// Limpieza automática (eliminar datos de prueba)
// ─────────────────────────────────────────────

echo "\n--- Limpiando datos de prueba ---\n";

try {
    if ($idCancionPrueba) {
        $pdo->prepare("DELETE FROM canciones WHERE id_cancion = :id")
            ->execute([':id' => $idCancionPrueba]);
        echo "  Canción de prueba eliminada (id={$idCancionPrueba})\n";
    }
    if ($idAlbumPrueba) {
        $pdo->prepare("DELETE FROM albumes WHERE id_album = :id")
            ->execute([':id' => $idAlbumPrueba]);
        echo "  Álbum de prueba eliminado (id={$idAlbumPrueba})\n";
    }
    if ($idUsuarioPrueba) {
        // Borrar posibles transacciones del usuario de prueba antes de borrar el usuario
        $pdo->prepare("DELETE FROM transacciones WHERE id_usuario_emisor = :id OR id_artista_receptor = :id2")
            ->execute([':id' => $idUsuarioPrueba, ':id2' => $idUsuarioPrueba]);
        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id")
            ->execute([':id' => $idUsuarioPrueba]);
        echo "  Usuario de prueba eliminado (id={$idUsuarioPrueba})\n";
    }
} catch (PDOException $e) {
    echo "  [AVISO] Error en limpieza: " . $e->getMessage() . "\n";
}

// ─────────────────────────────────────────────
// Resumen final
// ─────────────────────────────────────────────

$total = $resultados['pasados'] + $resultados['fallados'];
echo "\n========================================\n";
echo "RESUMEN: {$resultados['pasados']} pasados / {$resultados['fallados']} fallados / {$total} total\n";
echo "========================================\n";

exit($resultados['fallados'] > 0 ? 1 : 0);
