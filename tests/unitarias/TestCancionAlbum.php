<?php
/**
 * Pruebas Unitarias — Modelos Cancion y Album (funcionalidad)
 *
 * Prueba los métodos de negocio de Cancion y Album con stubs de PDO:
 * guardarCancion(), buscar(), obtenerCancionesPorGenero(), obtenerRecientes(),
 * Album::crearAlbum(), Album::obtenerAlbumesPorArtista().
 *
 * No necesita conexión real a la base de datos.
 *
 * Cómo ejecutar:
 *   php tests/unitarias/TestCancionAlbum.php
 */

require_once __DIR__ . '/../../src/modelo/Cancion.php';
require_once __DIR__ . '/../../src/modelo/Album.php';

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

function assert_es_array_no_vacio(string $desc, $obtenido): void {
    global $resultados;
    if (is_array($obtenido) && count($obtenido) > 0) {
        echo "[PASADO] {$desc}\n";
        $resultados['pasados']++;
    } else {
        echo "[FALLADO] {$desc} — Se esperaba array no vacío, obtenido: " . var_export($obtenido, true) . "\n";
        $resultados['fallados']++;
    }
}

// ─────────────────────────────────────────────
// Stubs de PDO (reutilizados en ambos modelos)
// ─────────────────────────────────────────────

class StmtStub {
    private $fetchData;
    private $execResult;

    public function __construct($fetchData = false, bool $execResult = true) {
        $this->fetchData  = $fetchData;
        $this->execResult = $execResult;
    }

    public function bindParam($param, &$val, $type = null): void {}
    public function execute(): bool { return $this->execResult; }
    public function fetch($mode = null) { return $this->fetchData; }
    public function fetchAll($mode = null): array {
        return is_array($this->fetchData) ? $this->fetchData : [];
    }
}

class PdoStub {
    private $fetchData;
    private $execResult;

    public function __construct($fetchData = false, bool $execResult = true) {
        $this->fetchData  = $fetchData;
        $this->execResult = $execResult;
    }

    public function prepare($sql): StmtStub {
        return new StmtStub($this->fetchData, $this->execResult);
    }

    public function query($sql): StmtStub {
        return new StmtStub($this->fetchData, $this->execResult);
    }

    public function fetchColumn() { return 0; }
}

// ─────────────────────────────────────────────
// BLOQUE A — Cancion::guardarCancion()
// ─────────────────────────────────────────────

echo "\n=== A. Cancion::guardarCancion() ===\n";

// Caso 1: inserción correcta → execute() true → guardarCancion devuelve true
$pdo = new PdoStub(false, true);
$cancion = new Cancion($pdo);
$resultado = $cancion->guardarCancion(1, 'Mi Canción', 'uploads/canciones/abc123.mp3', 'Rock', '00:03:45');
assert_igual(
    'guardarCancion() con datos válidos debe devolver true cuando la BD acepta la inserción',
    true,
    $resultado
);

// Caso 2: execute() falla (p.ej. id_album inexistente por FK) → devuelve false
$pdo = new PdoStub(false, false);
$cancion = new Cancion($pdo);
$resultado = $cancion->guardarCancion(999, 'Canción Huérfana', 'uploads/canciones/x.mp3', 'Pop', '00:02:00');
assert_igual(
    'guardarCancion() debe devolver false cuando la BD rechaza la inserción',
    false,
    $resultado
);

// Caso 3: la duración por defecto es válida si no se calcula (sin argumentos opcionales)
$pdo = new PdoStub(false, true);
$cancion = new Cancion($pdo);
$resultado = $cancion->guardarCancion(2, 'Tema Sin Duración', 'uploads/canciones/tema.mp3', 'Trap');
assert_igual(
    'guardarCancion() sin duración explícita usa el valor por defecto y devuelve true',
    true,
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE B — Cancion::buscar()
// ─────────────────────────────────────────────

echo "\n=== B. Cancion::buscar() ===\n";

$cancionesMock = [
    ['id_cancion' => 1, 'titulo' => 'Rock Clásico', 'genero' => 'Rock', 'nombre_artistico' => 'Artista A', 'titulo_album' => 'Álbum 1'],
    ['id_cancion' => 2, 'titulo' => 'Rock Moderno', 'genero' => 'Rock', 'nombre_artistico' => 'Artista B', 'titulo_album' => 'Álbum 2'],
];

// Caso 4: término con resultados → fetchAll devuelve array con canciones
$pdo = new PdoStub($cancionesMock, true);
$cancion = new Cancion($pdo);
$resultado = $cancion->buscar('Rock');
assert_igual(
    'buscar("Rock") debe devolver exactamente 2 canciones cuando el stub simula esos resultados',
    2,
    count($resultado)
);
assert_igual(
    'El primer resultado de buscar() debe tener el título correcto',
    'Rock Clásico',
    $resultado[0]['titulo']
);

// Caso 5: término sin resultados → fetchAll devuelve array vacío
$pdo = new PdoStub([], true);
$cancion = new Cancion($pdo);
$resultado = $cancion->buscar('xxxxxxxxxnoexiste');
assert_igual(
    'buscar() con término sin coincidencias debe devolver un array vacío (no false)',
    [],
    $resultado
);

// Caso 6: buscar() siempre devuelve array, nunca false ni null
$pdo = new PdoStub(false, true); // fetch devuelve false (no hay resultados)
$cancion = new Cancion($pdo);
$resultado = $cancion->buscar('nada');
assert_igual(
    'buscar() nunca debe devolver false o null, siempre un array',
    true,
    is_array($resultado)
);

// ─────────────────────────────────────────────
// BLOQUE C — Cancion::obtenerCancionesPorGenero()
// ─────────────────────────────────────────────

echo "\n=== C. Cancion::obtenerCancionesPorGenero() ===\n";

$cancionesGenero = [
    ['id_cancion' => 3, 'titulo' => 'Trap Life', 'genero' => 'Trap', 'nombre_artistico' => 'MC Zero'],
    ['id_cancion' => 4, 'titulo' => 'Street Flow', 'genero' => 'Trap', 'nombre_artistico' => 'MC Uno'],
    ['id_cancion' => 5, 'titulo' => 'Night Vibes', 'genero' => 'Trap', 'nombre_artistico' => 'MC Dos'],
];

// Caso 7: género con canciones → devuelve las 3 canciones del stub
$pdo = new PdoStub($cancionesGenero, true);
$cancion = new Cancion($pdo);
$resultado = $cancion->obtenerCancionesPorGenero('Trap');
assert_igual(
    'obtenerCancionesPorGenero("Trap") debe devolver 3 canciones según el stub',
    3,
    count($resultado)
);
assert_igual(
    'Cada canción del género debe tener el campo nombre_artistico',
    true,
    isset($resultado[0]['nombre_artistico'])
);

// Caso 8: género sin canciones → devuelve array vacío
$pdo = new PdoStub([], true);
$cancion = new Cancion($pdo);
$resultado = $cancion->obtenerCancionesPorGenero('GeneroInexistente');
assert_igual(
    'obtenerCancionesPorGenero() con género sin canciones debe devolver array vacío',
    [],
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE D — Cancion::obtenerRecientes()
// ─────────────────────────────────────────────

echo "\n=== D. Cancion::obtenerRecientes() ===\n";

$recientesMock = [
    ['id_cancion' => 10, 'titulo' => 'Nuevo Tema 1', 'genero' => 'Pop', 'nombre_artistico' => 'Pop Star'],
    ['id_cancion' => 9,  'titulo' => 'Nuevo Tema 2', 'genero' => 'R&B', 'nombre_artistico' => 'Soul Singer'],
    ['id_cancion' => 8,  'titulo' => 'Nuevo Tema 3', 'genero' => 'Trap', 'nombre_artistico' => 'Trap God'],
];

// Caso 9: devuelve las N canciones más recientes
$pdo = new PdoStub($recientesMock, true);
$cancion = new Cancion($pdo);
$resultado = $cancion->obtenerRecientes(3);
assert_igual(
    'obtenerRecientes(3) debe devolver 3 canciones según el stub',
    3,
    count($resultado)
);

// Caso 10: sin canciones recientes → array vacío
$pdo = new PdoStub([], true);
$cancion = new Cancion($pdo);
$resultado = $cancion->obtenerRecientes();
assert_igual(
    'obtenerRecientes() sin datos debe devolver array vacío',
    [],
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE E — Album::crearAlbum()
// ─────────────────────────────────────────────

echo "\n=== E. Album::crearAlbum() ===\n";

// Caso 11: inserción correcta
$pdo = new PdoStub(false, true);
$album = new Album($pdo);
$resultado = $album->crearAlbum(1, 'Mi Primer Álbum', 'uploads/portadas/portada.png');
assert_igual(
    'crearAlbum() con datos válidos debe devolver true cuando la BD acepta la inserción',
    true,
    $resultado
);

// Caso 12: fallo en BD → devuelve false
$pdo = new PdoStub(false, false);
$album = new Album($pdo);
$resultado = $album->crearAlbum(999, 'Álbum Sin Artista', 'uploads/portadas/x.png');
assert_igual(
    'crearAlbum() debe devolver false cuando la BD rechaza la inserción',
    false,
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE F — Album::obtenerAlbumesPorArtista()
// ─────────────────────────────────────────────

echo "\n=== F. Album::obtenerAlbumesPorArtista() ===\n";

$albumesMock = [
    ['id_album' => 5, 'titulo' => 'Álbum 2024', 'portada_ruta' => 'uploads/portadas/p1.png', 'id_artista' => 2],
    ['id_album' => 3, 'titulo' => 'Álbum 2023', 'portada_ruta' => 'uploads/portadas/p2.png', 'id_artista' => 2],
];

// Caso 13: artista con álbumes → devuelve 2 álbumes
$pdo = new PdoStub($albumesMock, true);
$album = new Album($pdo);
$resultado = $album->obtenerAlbumesPorArtista(2);
assert_igual(
    'obtenerAlbumesPorArtista() debe devolver 2 álbumes según el stub',
    2,
    count($resultado)
);
assert_igual(
    'El primer álbum devuelto debe tener el título correcto',
    'Álbum 2024',
    $resultado[0]['titulo']
);

// Caso 14: artista sin álbumes → devuelve array vacío
$pdo = new PdoStub([], true);
$album = new Album($pdo);
$resultado = $album->obtenerAlbumesPorArtista(99);
assert_igual(
    'obtenerAlbumesPorArtista() para artista sin álbumes debe devolver array vacío',
    [],
    $resultado
);

// ─────────────────────────────────────────────
// Resumen final
// ─────────────────────────────────────────────

$total = $resultados['pasados'] + $resultados['fallados'];
echo "\n========================================\n";
echo "RESUMEN: {$resultados['pasados']} pasados / {$resultados['fallados']} fallados / {$total} total\n";
echo "========================================\n";

exit($resultados['fallados'] > 0 ? 1 : 0);
