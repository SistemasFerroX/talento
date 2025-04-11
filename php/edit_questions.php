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

if (!isset($_GET['course_id'])) {
    die("Curso no especificado.");
}
$course_id = intval($_GET['course_id']);

// Obtener las preguntas del curso
$query = "SELECT * FROM questions WHERE course_id = $course_id ORDER BY id ASC";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Preguntas</title>
  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <style>
    .edit-questions-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #f0f0f0; }
    a.action-btn { margin-right: 8px; padding: 4px 8px; background: #4285f4; color: #fff; text-decoration: none; border-radius: 4px; }
    a.action-btn:hover { background: #3367d6; }
  </style>
</head>
<body>
  <a href="mis_cursos.php" class="back-button" style="position:fixed;top:20px;left:20px;">Volver</a>
  <div class="edit-questions-container">
    <h1>Editar Preguntas - Curso <?php echo $course_id; ?></h1>
    <?php if($result && $result->num_rows > 0): ?>
      <table>
        <tr>
          <th>ID</th>
          <th>Enunciado</th>
          <th>Calificación</th>
          <th>Acciones</th>
        </tr>
        <?php while($pregunta = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $pregunta['id']; ?></td>
            <td><?php echo htmlspecialchars($pregunta['enunciado']); ?></td>
            <td><?php echo $pregunta['porcentaje']; ?>%</td>
            <td>
              <a href="edit_question.php?id=<?php echo $pregunta['id']; ?>" class="action-btn">Editar</a>
              <a href="delete_question.php?id=<?php echo $pregunta['id']; ?>" class="action-btn" onclick="return confirm('¿Estás seguro de eliminar esta pregunta?');">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No se encontraron preguntas para este curso.</p>
    <?php endif; ?>
  </div>
</body>
</html>
