<?php
// URL base de la aplicación calculada dinámicamente a partir de la ruta del script actual.
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
    //
}

// ─── Clase de conexión PDO con patrón Singleton ───────────────────────────────
// El Singleton evita abrir múltiples conexiones a la BD en el mismo request.
//Creamos la clase Database
class Database
{
    private static $instance = null;
    //Creamos una caja vacia que iguala null, osea que esta vacia
    protected $host     = 'localhost';
    protected $user     = 'root';
    protected $password = '';
    protected $db       = 'soundplay';

    public function conectar()
    {
        if (self::$instance === null) {
            //Al usar self decimos que vamos a usar una funcion de este mismo objeto 
            $dns = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //Lanza excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //Limpia cuando devuelve un dato
                PDO::ATTR_EMULATE_PREPARES   => false, 
                //Evita sentencias emuladas, ya que si escribe coódigo malicioso mysql lo tratará como
                //simple texto
            ];
            try {
                self::$instance = new PDO($dns, $this->user, $this->password, $options);
            } catch (\PDOException $e) {
                error_log("Error de conexión BD: " . $e->getMessage());
                exit("Error al conectar con la base de datos. Contacte al administrador.");
            }
        }
        return self::$instance;
    }
}

// Helpers CSRF
//falsificación de Petición en Sitios Cruzados
function csrf_generar(){ 
    //Realizamos una función normal de php
    if (session_status() === PHP_SESSION_NONE) session_start();
    //PHP_SESSION_NONE: No hay sesión abierta
    //PHP_SESSION_ACTIVE: Hay sesión abierta
    //PHP_SESSION_DISABLES: Sesiones desactivadas por el servidor
    if (empty($_SESSION['csrf_token'])) {
    //Creamos la variable csrf_token en el servidor
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        //random_bytes(32) ->Transforma esa variable en 32bytes aleatorios 
        //usando el generador criptografico.
        //bin2hex -> Convertimos ese numero en hexadecimales, cada bytes se convierte en 2 caracteres     
        }
    return $_SESSION['csrf_token'];
//Devolvemos el token creado
}

function csrf_campo(){
//Decrlara la función
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_generar()) . '">';
///Aquí construimos un bloque
    }
//Verificamos que un token enviado por el formulario sea válido
function csrf_verificar(){
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        //Funcion de http que se envía al navegador
        exit('Solicitud rechazada: token de seguridad no válido.');
    }
}
?>