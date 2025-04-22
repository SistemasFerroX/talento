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

// Verificar que el usuario esté autenticado y sea profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}
require '../php/config.php'; // Asegúrate de que la ruta al archivo de configuración sea correcta

$profesor_id = $_SESSION['user_id'];
// Obtener la empresa del profesor desde la sesión
$empresaProfesor = $mysqli->real_escape_string($_SESSION['empresa']);
$message = '';

// Procesar inscripción (a través de GET)
if (isset($_GET['enroll_student'])) {
    $course_id  = (int)$_GET['course_id'];
    $student_id = (int)$_GET['student_id'];
    
    // Verificar que el curso sea del profesor
    $stmt = $mysqli->prepare("SELECT * FROM courses WHERE id = ? AND profesor_id = ?");
    $stmt->bind_param("ii", $course_id, $profesor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $message = "No tienes permisos para inscribir en este curso.";
    } else {
        // Verificar que el estudiante pertenezca a la misma empresa
        $stmtStudent = $mysqli->prepare("SELECT empresa FROM users WHERE id = ?");
        $stmtStudent->bind_param("i", $student_id);
        $stmtStudent->execute();
        $resultStudent = $stmtStudent->get_result();
        if ($resultStudent->num_rows === 0) {
            $message = "Estudiante no encontrado.";
        } else {
            $studentInfo = $resultStudent->fetch_assoc();
            if ($studentInfo['empresa'] !== $empresaProfesor) {
                $message = "El estudiante no pertenece a tu empresa.";
            } else {
                // Verificar si ya está inscrito
                $stmtCheck = $mysqli->prepare("SELECT * FROM enrollments WHERE course_id = ? AND user_id = ?");
                $stmtCheck->bind_param("ii", $course_id, $student_id);
                $stmtCheck->execute();
                $resEnroll = $stmtCheck->get_result();
                if ($resEnroll->num_rows > 0) {
                    $message = "El estudiante ya está matriculado en este curso.";
                } else {
                    // Insertar la inscripción
                    $stmtEnroll = $mysqli->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
                    $stmtEnroll->bind_param("ii", $student_id, $course_id);
                    if ($stmtEnroll->execute()) {
                        $message = "¡Estudiante inscrito correctamente!";
                    } else {
                        $message = "Error al inscribir: " . $mysqli->error;
                    }
                }
            }
        }
    }
}

// Procesar desinscripción (mediante GET)
if (isset($_GET['unenroll'])) {
    $enrollment_id = (int)$_GET['unenroll'];
    // Verificar que la inscripción pertenezca a un curso del profesor
    $stmt = $mysqli->prepare("SELECT e.id FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.id = ? AND c.profesor_id = ?");
    $stmt->bind_param("ii", $enrollment_id, $profesor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $message = "No tienes permisos para desinscribir a este estudiante.";
    } else {
        $stmtDelete = $mysqli->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmtDelete->bind_param("i", $enrollment_id);
        if ($stmtDelete->execute()) {
            $message = "Estudiante desinscrito exitosamente.";
        } else {
            $message = "Error al desinscribir: " . $mysqli->error;
        }
    }
}

// Consultar los cursos del profesor (solo los que haya creado)
$stmtCourses = $mysqli->prepare("SELECT id, nombre FROM courses WHERE profesor_id = ?");
$stmtCourses->bind_param("i", $profesor_id);
$stmtCourses->execute();
$courses = $stmtCourses->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Profesor</title>
  <!-- (Opcional) Font Awesome para íconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Enlace a tu CSS de dashboard -->
  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <style>
    /* Estilos generales */
    .top-header { 
      background: #003366;
      color: #fff;
      padding: 10px 20px;
      text-align: center;
    }
    .nav-bar {
      list-style: none;
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 10px;
    }
    .nav-bar li a {
      display: inline-flex;
      align-items: center;
      padding: 8px 12px;
      background: #fff;
      color: #003366;
      text-decoration: none;
      border: 1px solid #003366;
      border-radius: 4px;
      font-weight: bold;
      transition: background 0.3s, color 0.3s;
    }
    .nav-bar li a:hover {
      background: #003366;
      color: #fff;
    }
    .alert-message {
      background: #e9f7ef;
      border: 1px solid #c3e6cb;
      padding: 12px;
      color: #155724;
      text-align: center;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    .course-list {
      list-style-type: none;
      padding: 0;
    }
    .course-list li {
      border-bottom: 1px solid #ccc;
      padding: 10px 0;
    }
    .course-card {
      margin-bottom: 15px;
    }
    .course-card h3 {
      margin: 0 0 5px;
    }
    .course-card button,
    .course-card a {
      padding: 5px 10px;
      border: none;
      border-radius: 4px;
      background-color: #007BFF;
      color: #fff;
      cursor: pointer;
      text-decoration: none;
    }
    .course-card button:hover,
    .course-card a:hover {
      background-color: #0056b3;
    }
    .banner-profesor img {
      width: 100%;
      height: 120px;         /* O ajusta a 100px si lo quieres más pequeño */
      object-fit: cover;
    }

  </style>
</head>
<body>

  <!-- Banner completo -->
  <div class="banner banner-profesor">
    <img src="../images/talento2.png" alt="Banner Talento+">
  </div>

  <!-- Encabezado con saludo y navegación -->
  <header class="top-header">
    <h1>Bienvenido, Profesor <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    <ul class="nav-bar">
      <li><a href="create_course.php"><i class="fa fa-plus"></i> Crear Curso</a></li>
      <li><a href="mis_cursos.php"><i class="fa fa-book"></i> Ver Mis Cursos</a></li>
      <li><a href="perfil_profesor.php"><i class="fa fa-user"></i> Mi Perfil</a></li>
      <li><a href="forum.php"><i class="fa fa-comments"></i> Foro</a></li>
      <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
    </ul>
  </header>
  
  <main class="main-content">
    <h2>Escritorio Profesor</h2>
    <p>Aquí puedes crear cursos, gestionar evaluaciones y ver las calificaciones de tus estudiantes.</p>
    
    <?php if ($message != ''): ?>
      <div class="alert-message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <section class="courses-section">
      <h3>Tus Cursos</h3>
      <?php if ($courses->num_rows > 0): ?>
        <ul class="course-list">
          <?php while ($course = $courses->fetch_assoc()): ?>
            <li>
              <strong><?php echo htmlspecialchars($course['nombre']); ?></strong>
              <!-- Botón para mostrar la sección de estudiantes NO inscritos -->
              <button class="toggle-enroll" data-courseid="<?php echo $course['id']; ?>">
                Inscribir Estudiantes
              </button>
              
              <!-- Sección oculta: estudiantes no inscritos en este curso (filtrados por empresa) -->
              <div class="enroll-section" id="enroll-section-<?php echo $course['id']; ?>" style="display: none;">
                <?php
                  // Se seleccionan solo estudiantes de la misma empresa
                  $stmtNotEnrolled = $mysqli->prepare("
                    SELECT id, nombre_completo
                    FROM users
                    WHERE rol = 'estudiante'
                      AND empresa = ?
                      AND id NOT IN (
                        SELECT user_id FROM enrollments WHERE course_id = ?
                      )
                  ");
                  $stmtNotEnrolled->bind_param("si", $empresaProfesor, $course['id']);
                  $stmtNotEnrolled->execute();
                  $notEnrolled = $stmtNotEnrolled->get_result();
                  if ($notEnrolled->num_rows > 0):
                    echo "<ul class='students-not-enrolled'>";
                    while ($student = $notEnrolled->fetch_assoc()):
                      echo "<li>" . htmlspecialchars($student['nombre_completo']) .
                           " <a class='inscribir-btn' href='?enroll_student=1&course_id=" . $course['id'] .
                           "&student_id=" . $student['id'] . "'>Inscribir</a></li>";
                    endwhile;
                    echo "</ul>";
                  else:
                    echo "<p>No hay estudiantes disponibles para inscribir en este curso.</p>";
                  endif;
                ?>
              </div>
              
              <!-- Botón para mostrar la sección de estudiantes inscritos -->
              <button class="toggle-enrolled" data-courseid="<?php echo $course['id']; ?>">
                Ver Estudiantes Inscritos
              </button>
              
              <!-- Sección oculta: estudiantes inscritos en este curso -->
              <div class="enrolled-section" id="enrolled-section-<?php echo $course['id']; ?>" style="display: none;">
                <?php
                  $stmtEnrolled = $mysqli->prepare("
                    SELECT e.id as enrollment_id, u.nombre_completo
                    FROM enrollments e JOIN users u ON e.user_id = u.id
                    WHERE e.course_id = ?
                  ");
                  $stmtEnrolled->bind_param("i", $course['id']);
                  $stmtEnrolled->execute();
                  $enrolled = $stmtEnrolled->get_result();
                  if ($enrolled->num_rows > 0):
                    echo "<ul class='students-enrolled'>";
                    while ($enr = $enrolled->fetch_assoc()):
                      echo "<li>" . htmlspecialchars($enr['nombre_completo']) .
                           " <a class='desinscribir-btn' href='?unenroll=" . $enr['enrollment_id'] . "'>Desinscribir</a></li>";
                    endwhile;
                    echo "</ul>";
                  else:
                    echo "<p>No hay estudiantes inscritos en este curso.</p>";
                  endif;
                ?>
              </div>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No tienes cursos creados aún.</p>
      <?php endif; ?>
    </section>
  </main>
  
  <!-- JavaScript para togglear secciones -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle para mostrar/ocultar la sección de inscripción
      document.querySelectorAll('.toggle-enroll').forEach(function(button) {
        button.addEventListener('click', function() {
          var courseId = this.getAttribute('data-courseid');
          var section = document.getElementById('enroll-section-' + courseId);
          section.style.display = (section.style.display === 'block') ? 'none' : 'block';
        });
      });
      // Toggle para mostrar/ocultar la sección de estudiantes inscritos
      document.querySelectorAll('.toggle-enrolled').forEach(function(button) {
        button.addEventListener('click', function() {
          var courseId = this.getAttribute('data-courseid');
          var section = document.getElementById('enrolled-section-' + courseId);
          section.style.display = (section.style.display === 'block') ? 'none' : 'block';
        });
      });
    });
  </script>
</body>
</html>
