<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nombre, $email, $password])) {
        echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión.'); window.location='index.html';</script>";
    } else {
        echo "<script>alert('Error al registrar. El email puede estar duplicado.'); window.location='registro.html';</script>";
    }
}
?>