<?php
// config.php
$host = 'localhost';
$dbname = 'libreria_db';
$user = 'root';
$password = '#1515#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para recordar usuario con cookie
function recordarUsuario($user_id, $user_email, $user_rol, $user_nombre) {
    $token = bin2hex(random_bytes(32));
    $expiracion = time() + (30 * 24 * 60 * 60); // 30 días
    
    setcookie('user_token', $token, $expiracion, '/');
    setcookie('user_id', $user_id, $expiracion, '/');
    setcookie('user_email', $user_email, $expiracion, '/');
    setcookie('user_rol', $user_rol, $expiracion, '/');
    setcookie('user_nombre', $user_nombre, $expiracion, '/');
}

// Función para verificar cookie
function verificarCookie($pdo) {
    if (isset($_COOKIE['user_token']) && isset($_COOKIE['user_id'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['user_name'] = $_COOKIE['user_nombre'];
        $_SESSION['rol'] = $_COOKIE['user_rol'];
        return true;
    }
    return false;
}

// Función para cerrar sesión y eliminar cookie
function cerrarSesion() {
    session_destroy();
    setcookie('user_token', '', time() - 3600, '/');
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('user_email', '', time() - 3600, '/');
    setcookie('user_rol', '', time() - 3600, '/');
    setcookie('user_nombre', '', time() - 3600, '/');
}
?>