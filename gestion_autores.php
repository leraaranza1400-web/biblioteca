<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    if (verificarCookie($pdo)) {
        if ($_SESSION['rol'] != 'admin') {
            header("Location: index.html");
            exit();
        }
    } else {
        header("Location: index.html");
        exit();
    }
}

// Agregar autor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_autor'])) {
    $nombre = trim($_POST['nombre']);
    $nacionalidad = trim($_POST['nacionalidad']);
    
    if (!empty($nombre)) {
        $sql = "INSERT INTO autores (nombre, nacionalidad) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $nacionalidad]);
        $mensaje = "✅ Autor agregado exitosamente";
    } else {
        $error = "❌ El nombre del autor es obligatorio";
    }
}

// Eliminar autor
if (isset($_GET['delete'])) {
    $sql = "DELETE FROM autores WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['delete']]);
    $mensaje = "✅ Autor eliminado";
}

$autores = $pdo->query("SELECT * FROM autores ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Gestionar Autores</title>
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
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    <span>Inicio</span>
                </a>
                <a href="gestion_autores.php" class="nav-item active">
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
                <h1>✍️ Gestión de Autores</h1>
                <p>Registra nuevos autores o elimina los existentes en el catálogo</p>
            </div>
            
            <!-- Mensajes de éxito/error -->
            <?php if(isset($mensaje)): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Formulario para agregar autor -->
            <div class="library-card form-card">
                <div class="card-header">
                    <span class="card-icon-small">✍️</span>
                    <h3>Nuevo Autor</h3>
                </div>
                <form method="POST">
                    <div class="form-row">
                        <input type="text" name="nombre" placeholder="Nombre del autor" required>
                        <input type="text" name="nacionalidad" placeholder="Nacionalidad (opcional)">
                        <button type="submit" name="add_autor" class="btn-primary">Registrar Autor</button>
                    </div>
                </form>
            </div>
            
            <!-- Lista de autores -->
            <div class="library-card">
                <div class="card-header">
                    <span class="card-icon-small">📋</span>
                    <h3>Autores Registrados</h3>
                </div>
                
                <?php if(count($autores) > 0): ?>
                    <div class="table-responsive">
                        <table class="library-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Nacionalidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($autores as $autor): ?>
                                <tr>
                                    <td><?php echo $autor['id']; ?></td>
                                    <td><?php echo htmlspecialchars($autor['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($autor['nacionalidad'] ?? '—'); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $autor['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('¿Eliminar al autor \"<?php echo htmlspecialchars($autor['nombre']); ?>\"? Esta acción no eliminará sus libros pero quedarán sin autor.');">
                                            🗑️ Eliminar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📭</span>
                        <p>No hay autores registrados</p>
                        <p class="empty-hint">Agrega tu primer autor usando el formulario de arriba</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>