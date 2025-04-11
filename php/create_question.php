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

// Obtener el ID del curso desde GET o POST
$course_id = 0;
if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
} elseif (isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
}
$mensaje = "";

// Función para reorganizar el array de archivos (para imágenes)
function reArrayFiles(&$file_post) {
    $file_ary = [];
    $file_count = count($file_post['name']);
    $file_keys  = array_keys($file_post);
    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }
    return $file_ary;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reorganizar las imágenes (si se suben)
    $imagenes = [];
    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'][0])) {
        $imagenes = reArrayFiles($_FILES['imagen']);
    }
    
    // Procesar cada pregunta enviada en el arreglo "questions"
    foreach ($_POST['questions'] as $index => $qData) {
        $enunciado = $mysqli->real_escape_string($qData['enunciado']);
        $porcentaje = floatval($qData['porcentaje']);
        
        // Procesar la imagen correspondiente (opcional)
        $imagen_nombre = "";
        if (isset($imagenes[$index]) && $imagenes[$index]['error'] == UPLOAD_ERR_OK) {
            $uploadDir = "../images/questions/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $originalName = basename($imagenes[$index]['name']);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $uniqueName = uniqid("q_", true) . "." . $ext;
            $destPath = $uploadDir . $uniqueName;
            if (move_uploaded_file($imagenes[$index]['tmp_name'], $destPath)) {
                $imagen_nombre = $uniqueName;
            }
        }
        
        // Insertar la pregunta
        $query = "INSERT INTO questions (course_id, enunciado, imagen, porcentaje) 
                  VALUES ($course_id, '$enunciado', '$imagen_nombre', $porcentaje)";
        if ($mysqli->query($query)) {
            $question_id = $mysqli->insert_id;
            // Procesar las opciones, asegurándonos de que exista el array 'options'
            $opciones = isset($qData['options']) ? $qData['options'] : [];
            // Convertir el valor ingresado (1-4) a índice (0-3)
            $correcta = intval($qData['correcta']) - 1;
            foreach ($opciones as $optIndex => $opcion) {
                if (trim($opcion) != "") {
                    $opcion = $mysqli->real_escape_string($opcion);
                    $es_correcta = ($optIndex == $correcta) ? 1 : 0;
                    $queryOpt = "INSERT INTO options (question_id, texto, es_correcta) 
                                 VALUES ($question_id, '$opcion', $es_correcta)";
                    $mysqli->query($queryOpt);
                }
            }
        }
    }
    $mensaje = "Todas las preguntas han sido guardadas.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Preguntas - Examen</title>
  <!-- Fuente Roboto -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Enlace al CSS -->
  <link rel="stylesheet" href="../css/create_question.css">
  <script>
    let questionCount = 1; // Se inicia con 1 bloque (índice 0 ya presente)
    function addQuestion() {
      const container = document.getElementById('questions-container');
      const template = document.getElementById('question-template');
      const clone = template.cloneNode(true);
      clone.removeAttribute('id');
      clone.style.display = 'block';
      // Actualizar el título de la pregunta
      const title = clone.querySelector('.question-title');
      title.textContent = 'Pregunta ' + (questionCount + 1);
      // Actualizar los atributos "name" de los inputs usando el índice correcto
      const inputs = clone.querySelectorAll('[data-name]');
      inputs.forEach(input => {
        let baseName = input.getAttribute('data-name');
        if (baseName.endsWith('[]')) {
          baseName = baseName.slice(0, -2);
          input.name = `questions[${questionCount}][${baseName}][]`;
        } else {
          input.name = `questions[${questionCount}][${baseName}]`;
        }
      });
      container.appendChild(clone);
      questionCount++;
    }
  </script>
</head>
<body>
  <!-- Botón fijo para volver al dashboard -->
  <a href="dashboard_profesor.php" class="back-button">Volver al Dashboard</a>
  
  <div class="container">
    <h1>Crear Preguntas</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <form action="create_question.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
      <div id="questions-container">
        <!-- Bloque inicial de pregunta -->
        <div class="question-block">
          <h2 class="question-title">Pregunta 1</h2>
          <div class="form-group">
            <label>Enunciado:</label>
            <textarea name="questions[0][enunciado]" data-name="enunciado" rows="3" placeholder="Escribe el enunciado" required></textarea>
          </div>
          <div class="form-group">
            <label>Calificación (%):</label>
            <input type="number" name="questions[0][porcentaje]" data-name="porcentaje" placeholder="Ej: 10" step="0.01" min="0" max="100" required>
          </div>
          <div class="form-group">
            <label>Imagen (opcional):</label>
            <input type="file" name="imagen[]" >
          </div>
          <div class="form-group">
            <label>Opciones de Respuesta:</label>
            <input type="text" name="questions[0][options][]" data-name="options[]" placeholder="Opción 1" required>
            <input type="text" name="questions[0][options][]" data-name="options[]" placeholder="Opción 2" required>
            <input type="text" name="questions[0][options][]" data-name="options[]" placeholder="Opción 3 (opcional)">
            <input type="text" name="questions[0][options][]" data-name="options[]" placeholder="Opción 4 (opcional)">
          </div>
          <div class="form-group">
            <label>Índice de la opción correcta (1-4):</label>
            <input type="number" name="questions[0][correcta]" data-name="correcta" min="1" max="4" required>
          </div>
        </div>
      </div>
      <button type="button" onclick="addQuestion()">Agregar otra pregunta</button>
      <button type="submit">Guardar Todas las Preguntas</button>
    </form>
    <p class="info">
      Examen – Cuestionario de selección múltiple.<br>
      Cada pregunta tiene una calificación en porcentaje.<br>
      Se considera aprobado si el resultado total es superior al 80%; de lo contrario, se repite el curso.<br>
      Los resultados se mostrarán en valor numérico en porcentaje.
    </p>
  </div>
  
  <!-- Plantilla oculta para nuevos bloques de pregunta -->
  <div id="question-template" style="display: none;">
    <div class="question-block">
      <h2 class="question-title">Pregunta X</h2>
      <div class="form-group">
        <label>Enunciado:</label>
        <textarea data-name="enunciado" rows="3" placeholder="Escribe el enunciado" required></textarea>
      </div>
      <div class="form-group">
        <label>Calificación (%):</label>
        <input type="number" data-name="porcentaje" placeholder="Ej: 10" step="0.01" min="0" max="100" required>
      </div>
      <div class="form-group">
        <label>Imagen (opcional):</label>
        <input type="file" name="imagen[]" >
      </div>
      <div class="form-group">
        <label>Opciones de Respuesta:</label>
        <input type="text" data-name="options[]" placeholder="Opción 1" required>
        <input type="text" data-name="options[]" placeholder="Opción 2" required>
        <input type="text" data-name="options[]" placeholder="Opción 3 (opcional)">
        <input type="text" data-name="options[]" placeholder="Opción 4 (opcional)">
      </div>
      <div class="form-group">
        <label>Índice de la opción correcta (1-4):</label>
        <input type="number" data-name="correcta" min="1" max="4" required>
      </div>
    </div>
  </div>
</body>
</html>
