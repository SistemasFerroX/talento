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

include '../db_connection.php'; // Conexión a la base de datos
$profesor_id = $_SESSION['user_id'];
$message = '';

// Procesar el formulario para matricular un estudiante
if (isset($_POST['enroll_student'])) {
    $student_id = intval($_POST['student_id']);
    $course_id  = intval($_POST['course_id']);

    // Verificar que el curso pertenezca al profesor
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND profesor_id = ?");
    $stmt->bind_param("ii", $course_id, $profesor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("No tienes permisos para matricular estudiantes en este curso.");
    }
    
    // Verificar si el estudiante ya está matriculado
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $course_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $message = "El estudiante ya está matriculado en este curso.";
    } else {
        // Insertar la matrícula
        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $course_id);
        if ($stmt->execute()) {
            $message = "Estudiante matriculado exitosamente.";
        } else {
            $message = "Error al matricular al estudiante.";
        }
    }
}

// Procesar desmatrícula mediante un parámetro GET
if (isset($_GET['unenroll'])) {
    $enrollment_id = intval($_GET['unenroll']);
    // Verificar que la matrícula corresponda a un curso del profesor
    $stmt = $conn->prepare("
        SELECT e.id 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.id = ? AND c.profesor_id = ?
    ");
    $stmt->bind_param("ii", $enrollment_id, $profesor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("No tienes permisos para desmatricular a este estudiante.");
    }
    // Eliminar la matrícula
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->bind_param("i", $enrollment_id);
    if ($stmt->execute()) {
        $message = "Estudiante desmatriculado exitosamente.";
    } else {
        $message = "Error al desmatricular al estudiante.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Matrículas</title>
  <link rel="stylesheet" href="../css/dashboard_profesor_from.css">
</head>
<body>
  <header class="top-header">
    <h1>Gestión de Matrículas</h1>
  </header>
  
  <main class="main-content">
    <?php if ($message !== '') { echo "<p>$message</p>"; } ?>
    
    <!-- Formulario para matricular a un estudiante -->
    <h2>Matricular a un Estudiante</h2>
    <form method="POST" action="">
        <label for="course_id">Curso:</label>
        <select name="course_id" id="course_id" required>
            <?php
            // Listar cursos del profesor
            $stmt = $conn->prepare("SELECT id, nombre FROM courses WHERE profesor_id = ?");
            $stmt->bind_param("i", $profesor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<option value='".$row['id']."'>".$row['nombre']."</option>";
            }
            ?>
        </select>
        <br><br>
        <label for="student_id">ID del Estudiante:</label>
        <input type="number" name="student_id" id="student_id" required>
        <br><br>
        <input type="submit" name="enroll_student" value="Matricular">
    </form>
    
    <!-- Listado de estudiantes matriculados en los cursos del profesor -->
    <h2>Estudiantes Matriculados</h2>
    <?php
    $stmt = $conn->prepare("
      SELECT e.id as enrollment_id, u.nombre_completo, c.nombre as course_name
      FROM enrollments e 
      JOIN courses c ON e.course_id = c.id
      JOIN users u ON e.user_id = u.id
      WHERE c.profesor_id = ?
    ");
    $stmt->bind_param("i", $profesor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>
                    <strong>".$row['nombre_completo']."</strong> en el curso <em>".$row['course_name']."</em>
                    <a href='?unenroll=".$row['enrollment_id']."'>Desmatricular</a>
                  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay estudiantes matriculados en tus cursos.</p>";
    }
    ?>
  </main>
</body>
</html>
