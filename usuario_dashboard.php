<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'usuario') {
    if (verificarCookie($pdo)) {
        if ($_SESSION['rol'] != 'usuario') {
            header("Location: index.html");
            exit();
        }
    } else {
        header("Location: index.html");
        exit();
    }
}

// Procesar préstamo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_prestamo'])) {
    $libro_id = $_POST['libro_id'];
    $usuario_id = $_SESSION['user_id'];
    $fecha_prestamo = date('Y-m-d');
    
    $sql = "SELECT ejemplares_disponibles FROM libros WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$libro_id]);
    $libro = $stmt->fetch();
    
    if ($libro['ejemplares_disponibles'] > 0) {
        $sql = "INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $libro_id, $fecha_prestamo]);
        
        $sql = "UPDATE libros SET ejemplares_disponibles = ejemplares_disponibles - 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$libro_id]);
        
        echo "<script>alert('📚 Préstamo registrado exitosamente');</script>";
    } else {
        echo "<script>alert('❌ No hay ejemplares disponibles');</script>";
    }
}

$libros_disponibles = $pdo->query("SELECT l.*, a.nombre as autor_nombre FROM libros l LEFT JOIN autores a ON l.autor_id = a.id WHERE l.ejemplares_disponibles > 0")->fetchAll();

$mis_prestamos = $pdo->prepare("SELECT p.*, l.titulo FROM prestamos p JOIN libros l ON p.libro_id = l.id WHERE p.usuario_id = ? ORDER BY p.fecha_prestamo DESC");
$mis_prestamos->execute([$_SESSION['user_id']]);
$prestamos = $mis_prestamos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Mi Cuenta</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar-library">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-icon">📚</span>
                    <span class="logo-text">Biblioteca</span>
                </div>
                <div class="user-badge user-badge-normal">
                    <span class="user-role">Lector</span>
                    <span class="user-name"><?php echo $_SESSION['user_name']; ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="usuario_dashboard.php" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    <span>Inicio</span>
                </a>
                <a href="logout.php" class="nav-item logout-btn">
                    <span class="nav-icon">🚪</span>
                    <span>Cerrar Sesión</span>
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1>Mis Préstamos</h1>
                <p>Solicita libros y consulta tu historial</p>
            </div>
            
            <!-- Solicitar préstamo -->
            <div class="library-card form-card">
                <h3>📖 Solicitar un libro</h3>
                <form method="POST" class="prestamo-form">
                    <select name="libro_id" required>
                        <option value="">Selecciona un libro disponible</option>
                        <?php foreach($libros_disponibles as $libro): ?>
                            <option value="<?= $libro['id'] ?>">
                                <?= $libro['titulo'] ?> - <?= $libro['autor_nombre'] ?> (<?= $libro['ejemplares_disponibles'] ?> disponibles)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="add_prestamo" class="btn-primary">
                        📌 Solicitar Préstamo
                    </button>
                </form>
            </div>
            
            <!-- Historial de préstamos -->
            <div class="library-card">
                <h3>📋 Mi historial de préstamos</h3>
                <div class="table-responsive">
                    <table class="library-table">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($prestamos) > 0): ?>
                                <?php foreach($prestamos as $p): ?>
                                <tr>
                                    <td><?= $p['titulo'] ?></td>
                                    <td><?= $p['fecha_prestamo'] ?></td>
                                    <td><?= $p['fecha_devolucion'] ?? '—' ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $p['estado'] ?>">
                                            <?= $p['estado'] == 'activo' ? '📘 Activo' : ($p['estado'] == 'devuelto' ? '✅ Devuelto' : '⚠️ Vencido') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-message">No tienes préstamos aún</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>