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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['course_id'])) {
    echo "Acceso no permitido.";
    exit;
}

$course_id = (int)$_POST['course_id'];
$user_id = $_SESSION['user_id'];

// Variables para calcular la calificación
$totalScore = 0.0;   // Puntos obtenidos
$totalWeight = 0.0;  // Suma de porcentajes de todas las preguntas

// Consultar todas las preguntas del curso
$queryQuestions = "SELECT * FROM questions WHERE course_id = $course_id";
$resultQuestions = $mysqli->query($queryQuestions);

if ($resultQuestions && $resultQuestions->num_rows > 0) {
    while ($question = $resultQuestions->fetch_assoc()) {
        $question_id = $question['id'];
        $weight = floatval($question['porcentaje']);  // Porcentaje asignado a esta pregunta
        $totalWeight += $weight;
        
        // El input en el formulario se nombró como "question_{id}"
        $inputName = 'question_' . $question_id;
        
        if (isset($_POST[$inputName])) {
            $submittedAnswer = trim($_POST[$inputName]);
            // Consultar la opción correcta para esta pregunta
            $stmt = $mysqli->prepare("SELECT texto FROM options WHERE question_id = ? AND es_correcta = 1 LIMIT 1");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $stmt->bind_result($correctAnswer);
            if ($stmt->fetch()) {
                // Comparar la respuesta enviada con la opción correcta (ignorando mayúsculas/minúsculas)
                if (strcasecmp(trim($submittedAnswer), trim($correctAnswer)) === 0) {
                    // Si es correcta, se suma el peso de la pregunta
                    $totalScore += $weight;
                }
            }
            $stmt->close();
        }
        // Si no se respondió la pregunta, se asume 0 puntos para ella
    }
}

// Calcular la calificación final como porcentaje
$finalGrade = 0;
if ($totalWeight > 0) {
    $finalGrade = ($totalScore / $totalWeight) * 100;
    $finalGrade = round($finalGrade, 2);
}

// Insertar la calificación en la tabla "grades"
$stmt = $mysqli->prepare("INSERT INTO grades (user_id, course_id, calificacion) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $user_id, $course_id, $finalGrade);
$stmt->execute();
$stmt->close();

// Define la nota mínima para aprobar (80%)
$notaMinimaAprobacion = 80;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultado del Quiz</title>
  <link rel="stylesheet" href="../css/quiz_result.css">
</head>
<body>
<div class="result-container">
  <h1>Resultado del Quiz</h1>
  <p>Tu calificación es: <strong><?php echo $finalGrade; ?>%</strong></p>
  
  <?php if ($finalGrade >= $notaMinimaAprobacion): ?>
    <p class="success-message">¡Felicidades! Has aprobado el curso.</p>
    <a href="dashboard_estudiante.php" class="btn">Ir al Dashboard</a>
  <?php else: ?>
    <p class="error-message">No has aprobado el curso. ¡Sigue intentando!</p>
    <a href="dashboard_estudiante.php" class="btn">Volver al Dashboard</a>
    <a href="course_content.php?course_id=<?php echo $course_id; ?>" class="btn">Repetir el Curso</a>
  <?php endif; ?>
</div>
</body>
</html>
