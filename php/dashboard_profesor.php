<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../login.html");
    exit;
}
require '../php/config.php';

$profesor_id     = $_SESSION['user_id'];
$empresaProfesor = $mysqli->real_escape_string($_SESSION['empresa']);
$message         = '';

// Procesar inscripción
if (isset($_GET['enroll_student'])) {
    $course_id  = (int)$_GET['course_id'];
    $student_id = (int)$_GET['student_id'];

    // Verificar curso del profesor
    $stmt = $mysqli->prepare("SELECT 1 FROM courses WHERE id = ? AND profesor_id = ?");
    $stmt->bind_param("ii", $course_id, $profesor_id);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        $message = "No tienes permisos para inscribir en este curso.";
    } else {
        // Verificar empresa del estudiante
        $stmt2 = $mysqli->prepare("SELECT empresa FROM users WHERE id = ?");
        $stmt2->bind_param("i", $student_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $info = $res2->fetch_assoc() ?: [];
        if (empty($info) || $info['empresa'] !== $empresaProfesor) {
            $message = "Estudiante no pertenece a tu empresa.";
        } else {
            // Ya inscrito?
            $stmt3 = $mysqli->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
            $stmt3->bind_param("ii", $course_id, $student_id);
            $stmt3->execute();
            if ($stmt3->get_result()->num_rows) {
                $message = "El estudiante ya está inscrito.";
            } else {
                // Insertar
                $stmt4 = $mysqli->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
                $stmt4->bind_param("ii", $student_id, $course_id);
                $stmt4->execute()
                    ? $message = "¡Estudiante inscrito correctamente!"
                    : $message = "Error al inscribir: " . $mysqli->error;
            }
        }
    }
}

// Procesar desinscripción
if (isset($_GET['unenroll'])) {
    $enrollment_id = (int)$_GET['unenroll'];
    $stmt = $mysqli->prepare("
      SELECT e.id FROM enrollments e
      JOIN courses c ON e.course_id = c.id
      WHERE e.id = ? AND c.profesor_id = ?
    ");
    $stmt->bind_param("ii", $enrollment_id, $profesor_id);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        $message = "No tienes permisos para desinscribir.";
    } else {
        $del = $mysqli->prepare("DELETE FROM enrollments WHERE id = ?");
        $del->bind_param("i", $enrollment_id);
        $del->execute()
            ? $message = "Estudiante desinscrito exitosamente."
            : $message = "Error al desinscribir: " . $mysqli->error;
    }
}

// Obtener cursos del profesor
$stmt = $mysqli->prepare("SELECT id, nombre FROM courses WHERE profesor_id = ?");
$stmt->bind_param("i", $profesor_id);
$stmt->execute();
$courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Profesor</title>
  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

  <div class="banner-profesor">
    <img src="../images/talento2.png" alt="Banner Talento+">
  </div>

  <header class="top-header">
    <h1>Bienvenido, Profesor <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
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
    <p>Aquí puedes gestionar tus cursos y estudiantes.</p>

    <?php if ($message): ?>
      <div class="alert-message"><?= $message ?></div>
    <?php endif; ?>

    <?php while ($course = $courses->fetch_assoc()): ?>
      <section class="courses-section">
        <h3><?= htmlspecialchars($course['nombre']) ?></h3>

        <button class="toggle-enroll" data-courseid="<?= $course['id'] ?>">
          Inscribir Estudiantes
        </button>

        <div class="enroll-section" id="enroll-<?= $course['id'] ?>">
          <div class="enroll-search-container">
            <input type="text" class="student-search" placeholder="🔍 Buscar por cédula…">
          </div>
          <ul class="students-not-enrolled">
            <?php
            $s = $mysqli->prepare("
              SELECT id, cedula, nombre_completo 
              FROM users 
              WHERE rol='estudiante' 
                AND empresa=? 
                AND id NOT IN (
                  SELECT user_id FROM enrollments WHERE course_id=?
                )
              ORDER BY cedula
            ");
            $s->bind_param("si", $empresaProfesor, $course['id']);
            $s->execute();
            $notEnrolled = $s->get_result();
            while ($st = $notEnrolled->fetch_assoc()):
            ?>
              <li>
                <a class="inscribir-btn"
                   href="?enroll_student=1&course_id=<?= $course['id'] ?>&student_id=<?= $st['id'] ?>">+</a>
                <span class="student-label">
                  <?= htmlspecialchars($st['cedula'] . ' – ' . $st['nombre_completo']) ?>
                </span>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>

        <button class="toggle-enrolled" data-courseid="<?= $course['id'] ?>">
          Ver Estudiantes Inscritos
        </button>

        <div class="enrolled-section" id="enrolled-<?= $course['id'] ?>">
          <ul class="students-enrolled">
            <?php
            $e = $mysqli->prepare("
              SELECT e.id AS eid, u.cedula, u.nombre_completo
              FROM enrollments e
              JOIN users u ON e.user_id = u.id
              WHERE e.course_id = ?
              ORDER BY u.cedula
            ");
            $e->bind_param("i", $course['id']);
            $e->execute();
            $enrolled = $e->get_result();
            while ($en = $enrolled->fetch_assoc()):
            ?>
              <li>
                <span class="student-label">
                  <?= htmlspecialchars($en['cedula'] . ' – ' . $en['nombre_completo']) ?>
                </span>
                <a class="desinscribir-btn" href="?unenroll=<?= $en['eid'] ?>">✕</a>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </section>
    <?php endwhile; ?>
  </main>

  <script>
    document.querySelectorAll('.toggle-enroll').forEach(btn => {
      btn.onclick = () => {
        let id = btn.dataset.courseid;
        document.getElementById('enroll-'+id).classList.toggle('visible');
      };
    });
    document.querySelectorAll('.toggle-enrolled').forEach(btn => {
      btn.onclick = () => {
        let id = btn.dataset.courseid;
        document.getElementById('enrolled-'+id).classList.toggle('visible');
      };
    });
    document.querySelectorAll('.student-search').forEach(input => {
      input.oninput = () => {
        let val = input.value.trim().toLowerCase();
        let list = input.closest('.enroll-section').querySelectorAll('li');
        list.forEach(li => {
          li.style.display = li.querySelector('.student-label').textContent
                              .toLowerCase().includes(val) ? '' : 'none';
        });
      };
    });
  </script>
</body>
</html>
