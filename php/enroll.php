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

if (isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verificar si el estudiante ya está inscrito en el curso
    $checkQuery = "SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id";
    $resultCheck = $mysqli->query($checkQuery);
    
    if ($resultCheck && $resultCheck->num_rows > 0) {
        // Si ya está inscrito, podemos redirigir o notificar
        echo "Ya estás inscrito en este curso.";
    } else {
        // Insertar la inscripción en la tabla enrollments
        $insertQuery = "INSERT INTO enrollments (user_id, course_id) VALUES ($user_id, $course_id)";
        if ($mysqli->query($insertQuery)) {
            // Redirigir a un dashboard o a la página del curso
            header("Location: dashboard_estudiante.php");
            exit;
        } else {
            echo "Error al inscribirse: " . $mysqli->error;
        }
    }
} else {
    echo "No se especificó el ID del curso.";
}
?>
