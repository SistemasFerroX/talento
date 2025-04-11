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

if (!isset($_GET['course_id'])) {
    echo "No se especificó el ID del curso.";
    exit;
}

$course_id = (int) $_GET['course_id'];
$user_id = $_SESSION['user_id'];

// Verificar que el estudiante esté inscrito en el curso
$checkQuery = "SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id";
$resCheck = $mysqli->query($checkQuery);
if (!$resCheck || $resCheck->num_rows == 0) {
    echo "No estás inscrito en este curso. <a href='dashboard_estudiante.php'>Regresar</a>";
    exit;
}

// Obtener la información del curso (creado por el profesor)
$queryCourse = "SELECT * FROM courses WHERE id = $course_id";
$resultCourse = $mysqli->query($queryCourse);
if (!$resultCourse || $resultCourse->num_rows == 0) {
    echo "El curso no existe.";
    exit;
}
$course = $resultCourse->fetch_assoc();

// Obtener materiales de apoyo (documentos y videos)
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

// Obtener las preguntas del curso
$queryQuestions = "SELECT * FROM questions WHERE course_id = $course_id";
$resultQuestions = $mysqli->query($queryQuestions);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Contenido del Curso - <?php echo htmlspecialchars($course['nombre']); ?></title>
  <link rel="stylesheet" href="../css/course_content.css">
</head>
<body>
  <header class="course-header">
    <h1><?php echo htmlspecialchars($course['nombre']); ?></h1>
  </header>
  
  <main class="course-content">
    <!-- Información del Curso -->
    <section class="course-info">
      <h2>Descripción del Curso</h2>
      <p><?php echo htmlspecialchars($course['descripcion']); ?></p>
    </section>
    
    <!-- Materiales de Apoyo -->
    <section class="materials">
      <h2>Materiales de Apoyo</h2>
      <?php if (!empty($documents)): ?>
        <div class="documents">
          <h3>Documentos</h3>
          <ul>
            <?php foreach ($documents as $doc): ?>
              <li><a href="../documents/<?php echo $doc; ?>" target="_blank"><?php echo $doc; ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <p>No hay documentos de apoyo para este curso.</p>
      <?php endif; ?>
      
      <?php if (!empty($videos)): ?>
        <div class="videos">
          <h3>Videos</h3>
          <ul>
            <?php foreach ($videos as $vid): ?>
              <li><a href="<?php echo $vid; ?>" target="_blank"><?php echo $vid; ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <p>No hay videos de apoyo para este curso.</p>
      <?php endif; ?>
    </section>
    
    <!-- Cuestionario / Contenido interactivo -->
    <section class="interactive-content">
      <h2>Cuestionario</h2>
      <?php if ($resultQuestions && $resultQuestions->num_rows > 0): ?>
      <form action="submit_quiz.php" method="POST">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <?php while ($question = $resultQuestions->fetch_assoc()): ?>
          <div class="question">
            <p><strong><?php echo htmlspecialchars($question['enunciado']); ?></strong></p>
            <?php
              // Para cada pregunta, obtener las opciones desde la tabla "options"
              $question_id = $question['id'];
              $optionsQuery = "SELECT * FROM options WHERE question_id = $question_id";
              $resultOptions = $mysqli->query($optionsQuery);
              if ($resultOptions && $resultOptions->num_rows > 0) {
                  // Suponemos que es una pregunta de opción única
                  while ($option = $resultOptions->fetch_assoc()) {
                      echo '<label>';
                      echo '<input type="radio" name="question_' . $question_id . '" value="' . htmlspecialchars($option['texto']) . '" required> ';
                      echo htmlspecialchars($option['texto']);
                      echo '</label><br>';
                  }
              } else {
                  echo "No hay opciones para esta pregunta.";
              }
            ?>
          </div>
        <?php endwhile; ?>
        <button type="submit" class="btn-submit">Enviar Respuestas</button>
      </form>
      <?php else: ?>
        <p>El profesor aún no ha creado preguntas para este curso.</p>
      <?php endif; ?>
    </section>
  </main>
  
  <footer class="course-footer">
    <a href="dashboard_estudiante.php" class="btn-back">Regresar al Dashboard</a>
  </footer>
</body>
</html>
