<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $recordar = isset($_POST['recordar']) ? true : false;
    
    $sql = "SELECT * FROM usuarios WHERE email = ? AND password = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        
        // Guardar cookie si el usuario marcó "Recordarme"
        if ($recordar) {
            recordarUsuario($user['id'], $user['email'], $user['rol'], $user['nombre']);
        }
        
        if ($user['rol'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: usuario_dashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Credenciales incorrectas'); window.location='index.html';</script>";
    }
} elseif (verificarCookie($pdo)) {
    // Si hay cookie válida, redirigir automáticamente
    if ($_SESSION['rol'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: usuario_dashboard.php");
    }
    exit();
} else {
    header("Location: index.html");
    exit();
}
?>