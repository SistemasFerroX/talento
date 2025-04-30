<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

if (!isset($_GET['id'])) {
    die("Curso no especificado.");
}
$curso_id = intval($_GET['id']);
$query = "SELECT * FROM courses WHERE id = $curso_id";
$result = $mysqli->query($query);
if (!$result || $result->num_rows != 1) {
    die("Curso no encontrado.");
}
$curso = $result->fetch_assoc();
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $descripcion = $mysqli->real_escape_string($_POST['descripcion']);
    $query_update = "UPDATE courses SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = $curso_id";
    if ($mysqli->query($query_update)) {
        $mensaje = "Curso actualizado exitosamente.";
        // Actualizar datos para mostrarlos en el formulario
        $curso['nombre'] = $nombre;
        $curso['descripcion'] = $descripcion;
    } else {
        $mensaje = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Curso</title>
  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <style>
    .edit-container { max-width: 600px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .edit-container h1 { text-align: center; margin-bottom: 20px; }
    .edit-container label { display: block; margin-bottom: 5px; font-weight: 500; }
    .edit-container input[type="text"], .edit-container textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
    .edit-container button { padding: 10px 20px; background: #4285f4; border: none; border-radius: 4px; color: #fff; cursor: pointer; }
    .edit-container button:hover { background: #3367d6; }
    .mensaje { text-align: center; font-weight: 500; margin-bottom: 15px; }
  </style>
</head>
<body>
  <a href="mis_cursos.php" class="back-button" style="position:fixed;top:20px;left:20px;">Volver</a>
  <div class="edit-container">
    <h1>Editar Curso</h1>
    <?php if(!empty($mensaje)): ?>
      <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <form action="edit_course.php?id=<?php echo $curso_id; ?>" method="POST">
      <label for="nombre">Nombre del Curso:</label>
      <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($curso['nombre']); ?>" required>
      <label for="descripcion">Descripción:</label>
      <textarea name="descripcion" id="descripcion" rows="5" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
      <button type="submit">Actualizar Curso</button>
    </form>
  </div>
</body>
</html>
