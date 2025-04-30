<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

if (!isset($_GET['course_id'])) {
    echo "No se especificó el ID del curso.";
    exit;
}

$course_id = (int)$_GET['course_id'];
$user_id = $_SESSION['user_id'];
$student_empresa = $mysqli->real_escape_string($_SESSION['empresa']); // Empresa del estudiante

// Traer datos del curso
$queryCourse = "SELECT * FROM courses WHERE id = $course_id";
$resultCourse = $mysqli->query($queryCourse);

if (!$resultCourse || $resultCourse->num_rows == 0) {
    echo "El curso no existe o no se pudo consultar.";
    exit;
}

$course = $resultCourse->fetch_assoc();

// Verificar que el curso pertenezca a la misma empresa que el estudiante
if ($course['empresa'] !== $student_empresa) {
    echo "No tienes permiso para ver este curso.";
    exit;
}

// Traer materiales (documents, videos)
$queryMaterials = "SELECT * FROM course_materials WHERE course_id = $course_id";
$resultMaterials = $mysqli->query($queryMaterials);

$documents = [];
$videos = [];

if ($resultMaterials) {
    while ($mat = $resultMaterials->fetch_assoc()) {
        if ($mat['material_type'] == 'document') {
            $documents[] = $mat['material_value'];
        } elseif ($mat['material_type'] == 'video') {
            $videos[] = $mat['material_value'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle del Curso</title>
  <!-- Puedes enlazar tu CSS -->
  <link rel="stylesheet" href="../css/course_detail.css">
</head>
<body>
  <h1>Detalle del Curso</h1>
  
  <div class="course-info">
    <h2><?php echo htmlspecialchars($course['nombre']); ?></h2>
    <p><?php echo htmlspecialchars($course['descripcion']); ?></p>
  </div>
  
  <div class="materials">
    <h3>Documentos de Apoyo</h3>
    <?php if (!empty($documents)): ?>
      <ul>
        <?php foreach ($documents as $doc): ?>
          <li><a href="../documents/<?php echo htmlspecialchars($doc); ?>" target="_blank">Descargar: <?php echo htmlspecialchars($doc); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay documentos de apoyo para este curso.</p>
    <?php endif; ?>
    
    <h3>Videos de Apoyo</h3>
    <?php if (!empty($videos)): ?>
      <ul>
        <?php foreach ($videos as $vid): ?>
          <li><a href="<?php echo htmlspecialchars($vid); ?>" target="_blank"><?php echo htmlspecialchars($vid); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay videos de apoyo para este curso.</p>
    <?php endif; ?>
  </div>
  
  <!-- Botón para confirmar la inscripción -->
  <a href="enroll.php?course_id=<?php echo $course_id; ?>" class="btn-confirm">
    Confirmar Inscripción
  </a>
</body>
</html>
