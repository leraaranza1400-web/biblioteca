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

// Agregar libro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_libro'])) {
    $titulo = trim($_POST['titulo']);
    $isbn = !empty($_POST['isbn']) ? trim($_POST['isbn']) : null;
    $autor_id = $_POST['autor_id'];
    
    if (!empty($titulo) && $autor_id > 0) {
        try {
            $sql = "INSERT INTO libros (titulo, isbn, autor_id, ejemplares_disponibles) VALUES (?, ?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $isbn, $autor_id]);
            $mensaje = "✅ Libro agregado exitosamente";
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "❌ Ya existe un libro con ese ISBN";
            } else {
                $error = "❌ Error al registrar el libro";
            }
        }
    } else {
        $error = "❌ El título y el autor son obligatorios";
    }
}

// Eliminar libro
if (isset($_GET['delete'])) {
    $sql = "DELETE FROM libros WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['delete']]);
    $mensaje = "✅ Libro eliminado";
}

$libros = $pdo->query("SELECT l.*, a.nombre as autor_nombre FROM libros l LEFT JOIN autores a ON l.autor_id = a.id ORDER BY l.id DESC")->fetchAll();
$autores = $pdo->query("SELECT * FROM autores ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Gestionar Libros</title>
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
                <a href="gestion_autores.php" class="nav-item">
                    <span class="nav-icon">✍️</span>
                    <span>Gestionar Autores</span>
                </a>
                <a href="gestion_libros.php" class="nav-item active">
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
                <h1>📖 Gestión de Libros</h1>
                <p>Registra nuevos libros y manten el inventario actualizado</p>
            </div>
            
            <!-- Mensajes de éxito/error -->
            <?php if(isset($mensaje)): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Formulario para agregar libro -->
            <div class="library-card form-card">
                <div class="card-header">
                    <span class="card-icon-small">📖</span>
                    <h3>Nuevo Libro</h3>
                </div>
                <form method="POST">
                    <div class="form-grid">
                        <input type="text" name="titulo" placeholder="Título del libro" required>
                        <input type="text" name="isbn" placeholder="ISBN (opcional)">
                        <select name="autor_id" required>
                            <option value="">Selecciona un autor</option>
                            <?php foreach($autores as $autor): ?>
                                <option value="<?php echo $autor['id']; ?>">
                                    <?php echo htmlspecialchars($autor['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="add_libro" class="btn-primary">Registrar Libro</button>
                    </div>
                </form>
            </div>
            
            <!-- Lista de libros -->
            <div class="library-card">
                <div class="card-header">
                    <span class="card-icon-small">📚</span>
                    <h3>Catálogo de Libros</h3>
                </div>
                
                <?php if(count($libros) > 0): ?>
                    <div class="table-responsive">
                        <table class="library-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>ISBN</th>
                                    <th>Autor</th>
                                    <th>Disponibles</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($libros as $libro): ?>
                                <tr>
                                    <td><?php echo $libro['id']; ?></td>
                                    <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($libro['isbn'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($libro['autor_nombre'] ?? 'Sin autor'); ?></td>
                                    <td>
                                        <span class="stock-badge stock-<?php echo $libro['ejemplares_disponibles'] > 0 ? 'available' : 'none'; ?>">
                                            <?php echo $libro['ejemplares_disponibles']; ?> ejemplar(es)
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?delete=<?php echo $libro['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('¿Eliminar el libro \"<?php echo htmlspecialchars($libro['titulo']); ?>\"? Los préstamos asociados también se eliminarán.');">
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
                        <p>No hay libros registrados</p>
                        <p class="empty-hint">Agrega tu primer libro usando el formulario de arriba</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Advertencia si no hay autores -->
            <?php if(count($autores) == 0): ?>
                <div class="alert alert-warning">
                    ⚠️ No hay autores registrados. 
                    <a href="gestion_autores.php">Registra un autor primero</a> antes de agregar libros.
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>