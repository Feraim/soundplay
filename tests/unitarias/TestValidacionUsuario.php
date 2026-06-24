<?php
/**
 * Pruebas Unitarias — Validación del modelo Usuario
 *
 * Valida la lógica de negocio de Usuario::validarRegistro() de forma aislada,
 * sin necesidad de conexión real a la base de datos.
 *
 * Cómo ejecutar:
 *   php tests/unitarias/TestValidacionUsuario.php
 */

// Incluir el modelo (solo la clase, sin la conexión real a BD)
require_once __DIR__ . '/../../src/modelo/Usuario.php';

// ─────────────────────────────────────────────
// Mini-framework de pruebas en línea
// ─────────────────────────────────────────────

/** Contador global de resultados */
$resultados = ['pasados' => 0, 'fallados' => 0];

/**
 * Ejecuta una aserción y muestra el resultado en pantalla.
 *
 * @param string $descripcion Texto que describe qué se está comprobando
 * @param mixed  $esperado    Valor que se espera obtener
 * @param mixed  $obtenido    Valor real devuelto por el código bajo prueba
 */
function assert_igual(string $descripcion, $esperado, $obtenido): void {
    global $resultados;

    // Comparación estricta para evitar falsos positivos entre '' y false, 0 y false, etc.
    if ($esperado === $obtenido) {
        echo "[PASADO] {$descripcion}\n";
        $resultados['pasados']++;
    } else {
        // Mostrar los valores para facilitar la depuración
        $esperadoStr = var_export($esperado, true);
        $obtenidoStr = var_export($obtenido, true);
        echo "[FALLADO] {$descripcion}\n";
        echo "          Esperado: {$esperadoStr}\n";
        echo "          Obtenido: {$obtenidoStr}\n";
        $resultados['fallados']++;
    }
}

/**
 * Comprueba que el valor obtenido NO sea true (es decir, debe ser un string de error).
 * Útil para verificar que la validación rechaza entradas incorrectas.
 *
 * @param string $descripcion Texto que describe qué se está comprobando
 * @param mixed  $obtenido    Valor real devuelto por el código bajo prueba
 */
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
// Creación del stub de base de datos (sin conexión real)
// ─────────────────────────────────────────────

/**
 * Stub de conexión PDO para aislar las pruebas de la base de datos.
 * El modelo Usuario recibe la conexión en el constructor; usamos este
 * objeto vacío para no necesitar MariaDB durante los tests unitarios.
 */
class ConexionSimulada {
    // No hace nada; es solo para que el constructor de Usuario no falle
    public function prepare($sql) { return null; }
}

// Instanciar el modelo con la conexión simulada
$conexionFalsa = new ConexionSimulada();
$usuario = new Usuario($conexionFalsa);

// ─────────────────────────────────────────────
// BLOQUE A — Validación de formato de email
// ─────────────────────────────────────────────

echo "\n=== A. Validación de formato de email ===\n";

// Caso 1: email sin el símbolo @
$resultado = $usuario->validarRegistro('correosinalta', 'pass12345', 'user', 1);
assert_es_error('Email sin @ debe devolver error', $resultado);

// Caso 2: email sin dominio (falta la parte después del @)
$resultado = $usuario->validarRegistro('correo@', 'pass12345', 'user', 1);
assert_es_error('Email sin dominio debe devolver error', $resultado);

// Caso 3: email sin extensión de dominio (.com, .es, etc.)
$resultado = $usuario->validarRegistro('correo@dominio', 'pass12345', 'user', 1);
assert_es_error('Email sin extensión de dominio debe devolver error', $resultado);

// Caso 4: email completamente vacío
$resultado = $usuario->validarRegistro('', 'pass12345', 'user', 1);
assert_es_error('Email vacío debe devolver error', $resultado);

// Caso 5: email válido (no debe bloquearse por formato)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'user', 1);
assert_igual(
    'Email válido debe devolver true (superando la validación de formato)',
    true,
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE B — Validación de longitud de contraseña
// ─────────────────────────────────────────────

echo "\n=== B. Validación de longitud de contraseña ===\n";

// Caso 6: contraseña de solo 7 caracteres (por debajo del mínimo)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', '1234567', 'user', 1);
assert_es_error('Contraseña de 7 caracteres debe devolver error', $resultado);

// Caso 7: contraseña vacía
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', '', 'user', 1);
assert_es_error('Contraseña vacía debe devolver error', $resultado);

// Caso 8: contraseña exactamente de 8 caracteres (límite mínimo permitido)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', '12345678', 'user', 1);
assert_igual(
    'Contraseña de 8 caracteres debe pasar la validación',
    true,
    $resultado
);

// Caso 9: contraseña larga (más de 8 caracteres)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'contrasenaLarga999', 'user', 1);
assert_igual(
    'Contraseña larga (>8 caracteres) debe pasar la validación',
    true,
    $resultado
);

// ─────────────────────────────────────────────
// BLOQUE C — Validación de roles permitidos
// ─────────────────────────────────────────────

echo "\n=== C. Validación de roles ===\n";

// Caso 10: rol no existente en el sistema
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'superadmin', 1);
assert_es_error('Rol "superadmin" (no permitido) debe devolver error', $resultado);

// Caso 11: rol vacío
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', '', 1);
assert_es_error('Rol vacío debe devolver error', $resultado);

// Caso 12: rol 'user' (permitido)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'user', 1);
assert_igual('Rol "user" debe pasar la validación', true, $resultado);

// Caso 13: rol 'artista' (permitido)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'artista', 1);
assert_igual('Rol "artista" debe pasar la validación', true, $resultado);

// Caso 14: rol 'admin' (permitido)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'admin', 1);
assert_igual('Rol "admin" debe pasar la validación', true, $resultado);

// ─────────────────────────────────────────────
// BLOQUE D — Validación del consentimiento RGPD
// ─────────────────────────────────────────────

echo "\n=== D. Validación de consentimiento RGPD ===\n";

// Caso 15: consentimiento = 0 (sin marcar el checkbox)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'user', 0);
assert_es_error('Sin consentimiento RGPD (valor 0) debe devolver error', $resultado);

// Caso 16: consentimiento = 1 (checkbox marcado)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'user', 1);
assert_igual('Con consentimiento RGPD (valor 1) debe pasar la validación', true, $resultado);

// Caso 17: consentimiento = true (equivalente booleano)
$resultado = $usuario->validarRegistro('usuario@ejemplo.com', 'pass12345', 'user', true);
assert_igual('Con consentimiento RGPD (true) debe pasar la validación', true, $resultado);

// ─────────────────────────────────────────────
// BLOQUE E — Validación de hash de contraseña
// ─────────────────────────────────────────────

echo "\n=== E. Seguridad del hash de contraseñas (Bcrypt) ===\n";

// Caso 18: el hash no debe ser igual al texto plano
$contrasenaOriginal = 'miPassword123';
$hash = password_hash($contrasenaOriginal, PASSWORD_DEFAULT);
assert_igual(
    'El hash Bcrypt nunca debe coincidir con el texto plano original',
    false,
    ($hash === $contrasenaOriginal)
);

// Caso 19: password_verify debe validar el hash correcto
$verificacionCorrecta = password_verify($contrasenaOriginal, $hash);
assert_igual(
    'password_verify debe devolver true con la contraseña correcta',
    true,
    $verificacionCorrecta
);

// Caso 20: password_verify debe rechazar una contraseña incorrecta
$verificacionIncorrecta = password_verify('otraContrasena999', $hash);
assert_igual(
    'password_verify debe devolver false con una contraseña incorrecta',
    false,
    $verificacionIncorrecta
);

// ─────────────────────────────────────────────
// Resumen final
// ─────────────────────────────────────────────

$total = $resultados['pasados'] + $resultados['fallados'];
echo "\n========================================\n";
echo "RESUMEN: {$resultados['pasados']} pasados / {$resultados['fallados']} fallados / {$total} total\n";
echo "========================================\n";

// Código de salida según resultado (útil para CI/CD)
exit($resultados['fallados'] > 0 ? 1 : 0);
