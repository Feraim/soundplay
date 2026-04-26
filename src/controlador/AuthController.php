<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? 'login';
    $database = new Database();
    $db = $database->getConnection();

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        try {
            $stmt = $db->prepare("SELECT id_usuario, rol, password FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['usuario_id'] = $row['id_usuario'];
                    $_SESSION['rol'] = $row['rol'];
                    $_SESSION['email'] = $email;
                    
                    header("Location: ../../index.php?page=panel");
                    exit;
                } else {
                    header("Location: ../../index.php?page=login&error=1");
                    exit;
                }
            } else {
                header("Location: ../../index.php?page=login&error=1");
                exit;
            }
        } catch (PDOException $e) {
            header("Location: ../../index.php?page=login&error=db");
            exit;
        }
    } 
    else if ($action === 'registro') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = $_POST['rol'] ?? 'user';
        $nombre_artistico = trim($_POST['nombre_artistico'] ?? '');
        $localidad = trim($_POST['localidad'] ?? '');

        // Validación backend
        if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            header("Location: ../../index.php?page=registro&error=invalid");
            exit;
        }

        // Unique email
        $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if($stmt->rowCount() > 0) {
            header("Location: ../../index.php?page=registro&error=email_taken");
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $db->beginTransaction();
            $insUser = $db->prepare("INSERT INTO usuarios (email, password, rol) VALUES (:email, :pass, :rol)");
            $insUser->execute([':email' => $email, ':pass' => $hash, ':rol' => $rol]);
            
            $lastId = $db->lastInsertId();
            
            if ($rol === 'artista') {
                $insArt = $db->prepare("INSERT INTO artistas (id_artista, nombre_artistico, localidad) VALUES (:id, :nom, :loc)");
                $insArt->execute([':id' => $lastId, ':nom' => $nombre_artistico, ':loc' => $localidad]);
            }
            
            $db->commit();
            $_SESSION['usuario_id'] = $lastId;
            $_SESSION['rol'] = $rol;
            $_SESSION['email'] = $email;
            header("Location: ../../index.php?page=panel");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            header("Location: ../../index.php?page=registro&error=db");
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: ../../index.php?page=inicio");
    exit;
}
?>
