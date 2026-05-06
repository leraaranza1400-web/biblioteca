<?php
session_start();
require_once 'config.php';
cerrarSesion();
header("Location: index.html");
exit();
?>