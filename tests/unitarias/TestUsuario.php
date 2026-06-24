<?php
/**
 * Pruebas Unitarias — Modelo Usuario (funcionalidad)
 *
 * Prueba los métodos de negocio de Usuario con stubs de PDO:
 * registrar(), obtenerPorEmail(), banearUsuario(), actualizarContrasena().
 *
 * No necesita conexión real a la base de datos.
 *
 * Cómo ejecutar:
 *   php tests/unitarias/TestUsuario.php
 */

require_once __DIR__ . '/../../src/modelo/Usuario.php';

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
// Stubs de PDO
// ─────────────────────────────────────────────

/**
 * Stub de PDOStatement: simula prepare/execute/fetch sin tocar BD.
 * $fetchData  → lo que fetch() o fetchAll() devolverá
 * $execResult → lo que execute() devolverá (true = éxito, false = fallo)
 */
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

/**
 * Stub de PDO: devuelve el mismo StmtStub en cada llamada a prepare().
 * Permite configurar el resultado que devolverá el statement.
 */
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

    /** Necesario para asegurarColumnaBanned() y asegurarColumnaRecomendaciones() */
    public function query($sql) {
        return new StmtStub(null, true);
    }
    public function exec($sql): int { return 0; }
}

// ─────────────────────────────────────────────
// BLOQUE A — registrar()
// ─────────────────────────────────────────────

echo "\n=== A. Usuario::registrar() ===\n";

// Caso 1: inserción correcta → execute() devuelve true → registrar() devuelve true
$pdo = new PdoStub(false, true);
$usuario = new Usuario($pdo);
$resultado = $usuario->registrar('nuevo@ejemplo.com', 'password123', 'user', 1);
assert_igual(
    'registrar() con datos válidos debe devolver true cuando la BD acepta la inserción',
    true,
    $resultado
);

// Caso 2: execute() devuelve false (p.ej. violación de restricción) → registrar() devuelve false
$pdo = new PdoStub(false, false);
$usuario = new Usuario($pdo);
$resultado = $usuario->registrar('duplicado@ejemplo.com', 'password123', 'user', 1);
assert_igual(
    'registrar() debe devolver false cuando la BD rechaza la inserción',
    false,
    $resultado
);

// Caso 3: la contraseña almacenada es el hash, nunca el texto plano
//         Verificamos usando password_verify para confirmar que el modelo hace el hash internamente.
//         Usamos un PDO que "recuerde" el valor ligado (stub especializado).
class PdoCaptura {
    public $contrasenaGuardada = null;

    public function prepare($sql) {
        $self = $this;
        return new class($self) {
            private $parent;
            private $params = [];

            public function __construct($parent) { $this->parent = $parent; }
            public function bindParam($param, &$val, $type = null): void {
                $this->params[$param] = $val;
                if ($param === ':contrasena') {
                    $this->parent->contrasenaGuardada = $val;
                }
            }
            public function execute(): bool { return true; }
        };
    }
    public function query($sql) { return new StmtStub(); }
    public function exec($sql): int { return 0; }
}

$pdoCaptura = new PdoCaptura();
$usuario = new Usuario($pdoCaptura);
$usuario->registrar('test@ejemplo.com', 'miPassword123', 'user', 1);
$hashGuardado = $pdoCaptura->contrasenaGuardada;

assert_igual(
    'La contraseña guardada en BD nunca debe ser el texto plano original',
    false,
    ($hashGuardado === 'miPassword123')
);
assert_igual(
    'password_verify debe confirmar que el hash guardado corresponde a la contraseña original',
    true,
    password_verify('miPassword123', $hashGuardado)
);

// ─────────────────────────────────────────────
// BLOQUE B — obtenerPorEmail()
// ─────────────────────────────────────────────

echo "\n=== B. Usuario::obtenerPorEmail() ===\n";

$usuarioEsperado = [
    'id_usuario' => 7,
    'email'      => 'artista@ejemplo.com',
    'contrasena' => password_hash('segura123', PASSWORD_DEFAULT),
    'rol'        => 'artista',
    'banned'     => 0,
];

// Caso 5: email existente → fetch() devuelve el array → obtenerPorEmail devuelve el array
$pdo = new PdoStub($usuarioEsperado, true);
$usuario = new Usuario($pdo);
$resultado = $usuario->obtenerPorEmail('artista@ejemplo.com');
assert_igual(
    'obtenerPorEmail() debe devolver el array del usuario cuando el email existe',
    $usuarioEsperado,
    $resultado
);

// Caso 6: email inexistente → fetch() devuelve false → obtenerPorEmail devuelve false
$pdo = new PdoStub(false, true);
$usuario = new Usuario($pdo);
$resultado = $usuario->obtenerPorEmail('noexiste@ejemplo.com');
assert_igual(
    'obtenerPorEmail() debe devolver false cuando el email no existe en BD',
    false,
    $resultado
);

// Caso 7: password_verify con el hash devuelto por obtenerPorEmail
//         Simula el flujo real del login: obtienes el usuario y verificas la contraseña.
$hashReal = password_hash('claveSegura99', PASSWORD_DEFAULT);
$usuarioConHash = ['id_usuario' => 3, 'email' => 'u@u.com', 'contrasena' => $hashReal, 'rol' => 'user', 'banned' => 0];
$pdo = new PdoStub($usuarioConHash, true);
$usuario = new Usuario($pdo);
$obtenido = $usuario->obtenerPorEmail('u@u.com');
assert_igual(
    'password_verify con el hash devuelto debe autenticar la contraseña correcta',
    true,
    password_verify('claveSegura99', $obtenido['contrasena'])
);
assert_igual(
    'password_verify debe rechazar una contraseña incorrecta para el mismo hash',
    false,
    password_verify('claveErronea', $obtenido['contrasena'])
);

// ─────────────────────────────────────────────
// BLOQUE C — banearUsuario()
// ─────────────────────────────────────────────

echo "\n=== C. Usuario::banearUsuario() ===\n";

// Caso 9: baneo exitoso → execute() true → banearUsuario devuelve true
$pdo = new PdoStub(false, true);
$usuario = new Usuario($pdo);
$resultado = $usuario->banearUsuario('usuario@ejemplo.com', 1);
assert_igual(
    'banearUsuario() debe devolver true cuando la BD acepta el UPDATE',
    true,
    $resultado
);

// Caso 10: desbaneo exitoso
$pdo = new PdoStub(false, true);
$usuario = new Usuario($pdo);
$resultado = $usuario->banearUsuario('usuario@ejemplo.com', 0);
assert_igual(
    'banearUsuario() con banned=0 (desbaneo) debe devolver true',
    true,
    $resultado
);

// Caso 11: execute() falla → banearUsuario devuelve false
$pdo = new PdoStub(false, false);
$usuario = new Usuario($pdo);
$resultado = $usuario->banearUsuario('inexistente@ejemplo.com', 1);
assert_igual(
    'banearUsuario() debe devolver false cuando la BD falla',
    false,
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE D — actualizarContrasena()
// ─────────────────────────────────────────────

echo "\n=== D. Usuario::actualizarContrasena() ===\n";

// Caso 12: actualización correcta → devuelve true
$pdo = new PdoStub(false, true);
$usuario = new Usuario($pdo);
$nuevoHash = password_hash('nuevaClave456', PASSWORD_DEFAULT);
$resultado = $usuario->actualizarContrasena('usuario@ejemplo.com', $nuevoHash);
assert_igual(
    'actualizarContrasena() debe devolver true cuando la BD acepta el UPDATE',
    true,
    $resultado
);

// Caso 13: fallo en BD → devuelve false
$pdo = new PdoStub(false, false);
$usuario = new Usuario($pdo);
$resultado = $usuario->actualizarContrasena('noexiste@ejemplo.com', $nuevoHash);
assert_igual(
    'actualizarContrasena() debe devolver false cuando la BD falla',
    false,
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
