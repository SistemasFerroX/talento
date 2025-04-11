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

$student_id = $_SESSION['user_id'];
// Filtrar por la misma empresa del usuario
$empresa = $mysqli->real_escape_string($_SESSION['empresa']);

// Cursos disponibles (no inscritos) solo de la misma empresa
$query_available = "
    SELECT *
    FROM courses
    WHERE id NOT IN (
        SELECT course_id FROM enrollments WHERE user_id = $student_id
    )
    AND empresa = '$empresa'
";
$result_available = $mysqli->query($query_available);

// "Mis Cursos" (inscritos) que aún no están aprobados, filtrando por empresa
$query_enrolled = "
    SELECT 
      c.id, 
      c.nombre, 
      c.descripcion, 
      c.profesor_id, 
      c.fecha_creacion,
      (
        SELECT MAX(g.calificacion)
        FROM grades g
        WHERE g.course_id = c.id 
          AND g.user_id = $student_id
      ) AS calificacion
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = $student_id
      AND c.empresa = '$empresa'
    HAVING calificacion < 80 OR calificacion IS NULL
";
$result_enrolled = $mysqli->query($query_enrolled);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Estudiante</title>
  <link rel="stylesheet" href="../css/dashboard_estudiante.css">
  <!-- Puedes agregar aquí el CSS necesario para el botón de perfil si aún no lo tienes -->
  <style>
    /* Ejemplo de estilos para el botón "Mi Perfil" */
    .profile-btn {
      display: inline-block;
      padding: 8px 12px;
      background: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 600;
      transition: background 0.3s;
      margin-right: 10px;
    }
    .profile-btn:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

  <!-- Barra Superior -->
  <header class="top-bar">
    <div class="top-bar-left">
      <img src="../images/logo.png" alt="Logo" class="logo">
      <span class="site-name">Plataforma de Cursos</span>
    </div>
    <div class="top-bar-right">
      <span class="username">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
      <!-- Botón de Mi Perfil -->
      <a href="perfil_estudiante.php" class="profile-btn">Mi Perfil</a>
      <!-- Botón para acceder al Foro -->
      <a href="forum.php" class="forum-btn">Foro</a>
      <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>
  </header>

  <!-- Banner principal -->
  <div class="banner">
    <img src="../images/banner.png" alt="Banner Estudiante">
  </div>

  <!-- Breadcrumb -->
  <nav class="breadcrumb">
    <ul>
      <li><a href="dashboard_estudiante.php">Inicio</a></li>
      <li>Cursos</li>
    </ul>
  </nav>

  <!-- Contenedor principal en 3 columnas -->
  <main class="layout-container">
    <!-- Columna Izquierda: Agenda y Calendario -->
    <aside class="left-sidebar">
      <div class="agenda-container">
        <h3>Agenda del Curso</h3>
        <img src="../images/agenda_icon.png" alt="Icono Agenda" class="agenda-icon">
        <p>Aquí se mostrarán fechas y actividades próximas.</p>
      </div>
      <h3>Calendario</h3>
      <iframe 
        src="https://calendar.google.com/calendar/embed?src=c_ff2ce132667c7ffe8ac77b4bd3096df62ed6e22cc6bc23935ce6262f5813e2fd%40group.calendar.google.com&ctz=America%2FBogota"
        style="border:0" width="100%" height="300" frameborder="0" scrolling="no">
      </iframe>
    </aside>

    <!-- Columna Central: Cursos Disponibles y Mis Cursos -->
    <div class="main-content">
      <!-- Sección de cursos disponibles -->
      <section class="section-courses">
        <h2>Cursos Disponibles</h2>
        <?php if ($result_available && $result_available->num_rows > 0): ?>
          <div class="course-grid">
            <?php while ($course = $result_available->fetch_assoc()): ?>
              <div class="course-card">
                <h3><?php echo htmlspecialchars($course['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($course['descripcion']); ?></p>
                <a href="course_detail.php?course_id=<?php echo $course['id']; ?>" class="action-btn">Inscribirse</a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p>No hay cursos disponibles para inscribirse.</p>
        <?php endif; ?>
      </section>

      <!-- Sección de cursos inscritos (solo los que aún no están aprobados) -->
      <section class="section-courses">
        <h2>Mis Cursos</h2>
        <?php if ($result_enrolled && $result_enrolled->num_rows > 0): ?>
          <div class="course-grid">
            <?php while ($course = $result_enrolled->fetch_assoc()): ?>
              <?php 
                $cal = $course['calificacion']; 
                $buttonText = is_null($cal) ? "Acceder" : "Repetir";
              ?>
              <div class="course-card enrolled">
                <h3><?php echo htmlspecialchars($course['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($course['descripcion']); ?></p>
                <a href="course_content.php?course_id=<?php echo $course['id']; ?>" class="btn-acceder">
                  <?php echo $buttonText; ?>
                </a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p>No estás inscrito en ningún curso.</p>
        <?php endif; ?>
      </section>
    </div><!-- Fin .main-content -->

    <!-- Columna Derecha: Noticias de Interés -->
    <aside class="right-sidebar">
      <h3>Noticias de Interés</h3>
      <div class="video-container">
        <iframe 
          width="100%" 
          height="180" 
          src="https://www.youtube.com/watch?v=Oh7ffsA54mg" 
          frameborder="0" 
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
          allowfullscreen>
        </iframe>
      </div>
      <p>Aquí puedes colocar noticias relevantes o actualizaciones importantes para el estudiante.</p>
      <!-- Botón para ver cursos realizados y calificación -->
      <a href="cursos_realizados.php" class="btn-evaluacion">
        Ver cursos realizados y calificación
      </a>
    </aside>

  </main>

</body>
</html>
