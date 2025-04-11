<?php
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'domain'   => '',       // O 'localhost' si lo prefieres
  'secure'   => false,    // false, porque usas HTTP, no HTTPS
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$profesor_id = $_SESSION['user_id'];
$query = "SELECT * FROM courses WHERE profesor_id = $profesor_id ORDER BY fecha_creacion DESC";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Cursos</title>
  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <style>
    /* Puedes agregar estilos específicos para la tabla y acciones */
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #f0f0f0; }
    a.action-btn { margin-right: 8px; padding: 4px 8px; background: #4285f4; color: #fff; text-decoration: none; border-radius: 4px; }
    a.action-btn:hover { background: #3367d6; }
  </style>
</head>
<body>
  <header>
    <h1>Mis Cursos</h1>
    <a href="dashboard_profesor.php">Volver al Escritorio</a>
  </header>
  <main>
    <?php if($result && $result->num_rows > 0): ?>
      <table>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Fecha Creación</th>
          <th>Acciones</th>
        </tr>
        <?php while($curso = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $curso['id']; ?></td>
            <td><?php echo htmlspecialchars($curso['nombre']); ?></td>
            <td><?php echo htmlspecialchars($curso['descripcion']); ?></td>
            <td><?php echo $curso['fecha_creacion']; ?></td>
            <td>
              <a href="edit_course.php?id=<?php echo $curso['id']; ?>" class="action-btn">Editar Curso</a>
              <a href="delete_course.php?id=<?php echo $curso['id']; ?>" class="action-btn" onclick="return confirm('¿Estás seguro de eliminar este curso?');">Eliminar Curso</a>
              <a href="edit_questions.php?course_id=<?php echo $curso['id']; ?>" class="action-btn">Editar Preguntas</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No has creado ningún curso aún.</p>
    <?php endif; ?>
  </main>
</body>
</html>
