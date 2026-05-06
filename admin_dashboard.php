<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    if (verificarCookie($pdo)) {
        if ($_SESSION['rol'] == 'admin') {
            // Continúa
        } else {
            header("Location: index.html");
            exit();
        }
    } else {
        header("Location: index.html");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Panel Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar-library">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-icon">📚</span>
                    <span class="logo-text">Biblioteca</span>
                </div>
                <div class="user-badge admin-badge">
                    <span class="user-role">Administrador</span>
                    <span class="user-name"><?php echo $_SESSION['user_name']; ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    <span>Inicio</span>
                </a>
                <a href="gestion_autores.php" class="nav-item">
                    <span class="nav-icon">✍️</span>
                    <span>Gestionar Autores</span>
                </a>
                <a href="gestion_libros.php" class="nav-item">
                    <span class="nav-icon">📖</span>
                    <span>Gestionar Libros</span>
                </a>
                <a href="logout.php" class="nav-item logout-btn">
                    <span class="nav-icon">🚪</span>
                    <span>Cerrar Sesión</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Panel de Administración</h1>
                <p>Gestiona los autores y libros de la biblioteca</p>
            </div>
            
            <div class="cards-grid">
                <div class="library-card">
                    <div class="card-icon">✍️</div>
                    <h3>Gestión de Autores</h3>
                    <p>Registra nuevos autores y elimina los existentes en el catálogo.</p>
                    <a href="gestion_autores.php" class="card-btn">Ir a Autores →</a>
                </div>
                
                <div class="library-card">
                    <div class="card-icon">📖</div>
                    <h3>Gestión de Libros</h3>
                    <p>Agrega nuevos libros al sistema y manten el inventario actualizado.</p>
                    <a href="gestion_libros.php" class="card-btn">Ir a Libros →</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>