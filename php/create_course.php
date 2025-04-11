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

// Verificar que el usuario esté autenticado y que sea profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$mensaje = "";
// Recoger la empresa del profesor desde la sesión
$empresaProfesor = isset($_SESSION['empresa']) ? $_SESSION['empresa'] : "";

// Cuando se envía el formulario para crear el curso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $descripcion = $mysqli->real_escape_string($_POST['descripcion']);
    
    $profesor_id = $_SESSION['user_id'];
    
    // Insertar el curso en la tabla "courses"
    // Se añade la columna "empresa" usando la variable $empresaProfesor
    $query = "INSERT INTO courses (nombre, descripcion, profesor_id, fecha_creacion, empresa) 
              VALUES ('$nombre', '$descripcion', $profesor_id, NOW(), '$empresaProfesor')";
              
    if ($mysqli->query($query)) {
         $curso_id = $mysqli->insert_id;
         
         // Procesar documentos de apoyo (múltiples)
         if (isset($_FILES['documentos'])) {
             $uploadDir = "../documents/";
             if (!is_dir($uploadDir)) {
                 mkdir($uploadDir, 0777, true);
             }
             foreach ($_FILES['documentos']['tmp_name'] as $key => $tmp_name) {
                 if ($_FILES['documentos']['error'][$key] == UPLOAD_ERR_OK) {
                     $originalName = basename($_FILES['documentos']['name'][$key]);
                     $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                     $uniqueName = uniqid("doc_", true) . "." . $ext;
                     $destPath = $uploadDir . $uniqueName;
                     if (move_uploaded_file($tmp_name, $destPath)) {
                         // Insertar registro en "course_materials" para el documento
                         $stmt = $mysqli->prepare("INSERT INTO course_materials (course_id, material_type, material_value) VALUES (?, 'document', ?)");
                         $stmt->bind_param("is", $curso_id, $uniqueName);
                         $stmt->execute();
                         $stmt->close();
                     }
                 }
             }
         }
         
         // Procesar URLs de videos de apoyo
         if (isset($_POST['video_urls'])) {
             foreach ($_POST['video_urls'] as $video_url) {
                 $video_url = trim($video_url);
                 if (!empty($video_url)) {
                     $stmt = $mysqli->prepare("INSERT INTO course_materials (course_id, material_type, material_value) VALUES (?, 'video', ?)");
                     $stmt->bind_param("is", $curso_id, $video_url);
                     $stmt->execute();
                     $stmt->close();
                 }
             }
         }
         
         // Redirigir a la creación de preguntas o confirmar la creación del curso
         header("Location: create_question.php?course_id=" . $curso_id);
         exit;
    } else {
         $mensaje = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Curso</title>
  <!-- Fuente Roboto desde Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Enlace al CSS -->
  <link rel="stylesheet" href="../css/create_course_from.css">
</head>
<body>
  <!-- Botón fijo para volver al dashboard -->
  <a href="dashboard_profesor.php" class="back-button">Volver al Dashboard</a>

  <div class="container">
    <h1>Crear Curso</h1>
    <?php if(!empty($mensaje)): ?>
      <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <form action="create_course.php" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="nombre">Nombre del Curso:</label>
        <input type="text" name="nombre" id="nombre" placeholder="Ej. Programación Web" required>
      </div>
      <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" id="descripcion" rows="5" placeholder="Describe brevemente el curso" required></textarea>
      </div>
      <div class="form-group">
        <label for="documentos">Documentos de Apoyo (puedes subir varios):</label>
        <input type="file" name="documentos[]" id="documentos" multiple>
      </div>
      <div class="form-group">
        <label for="video_urls">URLs de Videos de Apoyo:</label>
        <!-- Puedes agregar dinámicamente más campos de URL con JavaScript si lo deseas -->
        <input type="url" name="video_urls[]" id="video_urls" placeholder="https://">
      </div>
      <button type="submit">Crear Curso</button>
    </form>
    <p class="info">Después de crear el curso, podrás agregar preguntas y otros recursos.</p>
  </div>
</body>
</html>
