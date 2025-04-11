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

// Verificar que el usuario esté autenticado y sea estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

if (isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];
    $user_id = $_SESSION['user_id'];
    
    // Obtener la empresa del estudiante desde la sesión
    $student_company = $mysqli->real_escape_string($_SESSION['empresa']);
    
    // Obtener la empresa del curso para comparar
    $queryCourse = "SELECT empresa FROM courses WHERE id = $course_id";
    $resultCourse = $mysqli->query($queryCourse);
    
    if (!$resultCourse || $resultCourse->num_rows == 0) {
        echo "El curso no existe o no se pudo consultar.";
        exit;
    }
    
    $course = $resultCourse->fetch_assoc();
    $course_company = $course['empresa'];
    
    // Verificar que el curso pertenezca a la misma empresa que el estudiante
    if ($course_company !== $student_company) {
        echo "No tienes permiso para inscribirte en este curso.";
        exit;
    }
    
    // Verificar si el estudiante ya está inscrito en el curso
    $checkQuery = "SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id";
    $resultCheck = $mysqli->query($checkQuery);
    
    if ($resultCheck && $resultCheck->num_rows > 0) {
        // Si ya está inscrito, notificar o redirigir
        echo "Ya estás inscrito en este curso.";
    } else {
        // Insertar la inscripción en la tabla enrollments
        $insertQuery = "INSERT INTO enrollments (user_id, course_id) VALUES ($user_id, $course_id)";
        if ($mysqli->query($insertQuery)) {
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
