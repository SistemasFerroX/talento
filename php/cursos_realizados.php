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

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$user_id = $_SESSION['user_id'];

// Consulta para obtener cursos con calificación >= 80
$query_completed = "
  SELECT c.id, c.nombre, c.descripcion, g.calificacion
  FROM grades g
  JOIN courses c ON c.id = g.course_id
  WHERE g.user_id = $user_id
    AND g.calificacion >= 80
";
$result_completed = $mysqli->query($query_completed);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Cursos Realizados</title>
  <!-- Asegúrate de que la ruta coincida con la ubicación real de tu archivo CSS -->
  <link rel="stylesheet" href="../css/cursos_realizados.css">
</head>
<body>
  <div class="result-container">
    <h1>Mis Cursos Realizados</h1>
    <a href="dashboard_estudiante.php" class="btn">Volver al Dashboard</a>
    <hr style="margin: 20px 0;">
    
    <?php if ($result_completed && $result_completed->num_rows > 0): ?>
      <table>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Calificación</th>
        </tr>
        <?php while ($row = $result_completed->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
            <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
            <td><?php echo htmlspecialchars($row['calificacion']); ?>%</td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No has completado ningún curso todavía.</p>
    <?php endif; ?>
  </div>
</body>
</html>
