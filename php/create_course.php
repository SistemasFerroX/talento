<?php
session_start();

// Solo profesores
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$mensaje = "";
$empresaProfesor = $_SESSION['empresa'] ?? "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ───────── Validar y subir PORTADA ───────── */
    if (!isset($_FILES['portada']) || $_FILES['portada']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = "Debes seleccionar una imagen de portada.";
    } else {
        $mime = mime_content_type($_FILES['portada']['tmp_name']);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'])) {
            $mensaje = "La portada debe ser JPG, PNG o WEBP.";
        }
    }

    if ($mensaje === "") {   // continuar solo si la portada es válida
        $nombre       = $mysqli->real_escape_string($_POST['nombre']);
        $descripcion  = $mysqli->real_escape_string($_POST['descripcion']);
        $profesor_id  = $_SESSION['user_id'];

        /* Subir la portada */
        $coversDir = __DIR__ . '/../uploads/covers/';
        if (!is_dir($coversDir)) mkdir($coversDir, 0777, true);

        $ext       = pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION);
        $coverName = uniqid("cover_", true) . "." . $ext;
        $destCover = $coversDir . $coverName;
        move_uploaded_file($_FILES['portada']['tmp_name'], $destCover);

        /* Insertar curso */
        $stmt = $mysqli->prepare(
          "INSERT INTO courses (nombre, descripcion, profesor_id, fecha_creacion, empresa, portada)
           VALUES (?,?,?,?,?,?)");
        $now = date('Y-m-d H:i:s');
        $stmt->bind_param('ssisss', $nombre, $descripcion, $profesor_id, $now, $empresaProfesor, $coverName);

        if ($stmt->execute()) {
            $curso_id = $stmt->insert_id;
            $stmt->close();

            /* ── Documentos múltiples ── */
            if (!empty($_FILES['documentos']['name'][0])) {
                $docsDir = __DIR__ . '/../documents/';
                if (!is_dir($docsDir)) mkdir($docsDir, 0777, true);

                foreach ($_FILES['documentos']['tmp_name'] as $k => $tmp) {
                    if ($_FILES['documentos']['error'][$k] == UPLOAD_ERR_OK) {
                        $ext   = pathinfo($_FILES['documentos']['name'][$k], PATHINFO_EXTENSION);
                        $docName = uniqid("doc_", true).".".$ext;
                        if (move_uploaded_file($tmp, $docsDir.$docName)) {
                            $q = $mysqli->prepare(
                                "INSERT INTO course_materials (course_id, material_type, material_value)
                                 VALUES (?,'document',?)");
                            $q->bind_param('is', $curso_id, $docName);
                            $q->execute();
                            $q->close();
                        }
                    }
                }
            }

            /* ── URLs de video ── */
            if (!empty($_POST['video_urls'])) {
                foreach ($_POST['video_urls'] as $url) {
                    $url = trim($url);
                    if ($url !== "") {
                        $q = $mysqli->prepare(
                             "INSERT INTO course_materials (course_id, material_type, material_value)
                              VALUES (?,'video',?)");
                        $q->bind_param('is', $curso_id, $url);
                        $q->execute();
                        $q->close();
                    }
                }
            }

            header("Location: create_question.php?course_id=".$curso_id);
            exit;

        } else {
            $mensaje = "Error al crear el curso: ".$stmt->error;
            $stmt->close();
            // opcional: eliminar la portada que se había subido
            @unlink($destCover);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Curso</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/create_course_from.css">
</head>
<body>
<a href="dashboard_profesor.php" class="back-button">Volver al Dashboard</a>

<div class="container">
  <h1>Crear Curso</h1>

  <?php if($mensaje): ?>
    <p class="mensaje"><?= $mensaje; ?></p>
  <?php endif; ?>

  <form action="create_course.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label for="nombre">Nombre del Curso:</label>
      <input type="text" name="nombre" id="nombre" placeholder="Ej. Programación Web" required>
    </div>

    <div class="form-group">
      <label for="descripcion">Descripción:</label>
      <textarea name="descripcion" id="descripcion" rows="4" placeholder="Describe brevemente el curso" required></textarea>
    </div>

    <div class="form-group">
      <label for="portada">Imagen de Portada (obligatoria):</label>
      <input type="file" name="portada" id="portada" accept="image/jpeg,image/png,image/webp" required>
    </div>

    <div class="form-group">
      <label for="documentos">Documentos de Apoyo (puedes subir varios):</label>
      <input type="file" name="documentos[]" id="documentos" multiple>
    </div>
    
    <div class="form-group">
      <label for="video_urls">URLs de Videos de Apoyo:</label>
      <input type="url" name="video_urls[]" id="video_urls" placeholder="https://">
    </div>

    <button type="submit">Crear Curso</button>
  </form>

  <p class="info">Después de crear el curso, podrás agregar preguntas y otros recursos.</p>
</div>
</body>
</html>
