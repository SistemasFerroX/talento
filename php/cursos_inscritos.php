<?php
// cursos_inscritos.php  — lista cursos que el estudiante puede tomar / repetir
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$student_id = $_SESSION['user_id'];
$empresa    = $mysqli->real_escape_string($_SESSION['empresa']);

/* Foto para la barra */
$fotoFile = $_SESSION['foto'] ?? '';
$fotoDisk = __DIR__.'/../uploads/'.$fotoFile;
$fotoURL  = ($fotoFile && file_exists($fotoDisk))
            ? '../uploads/'.rawurlencode($fotoFile)
            : '../images/default-avatar.png';

/* Cursos inscritos no aprobados (<80) */
$sql = "
  SELECT c.id, c.nombre, c.descripcion,
         (SELECT MAX(g.calificacion) FROM grades g
           WHERE g.course_id = c.id AND g.user_id = $student_id) AS calificacion
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
   WHERE e.user_id = $student_id
     AND c.empresa = '$empresa'
   HAVING calificacion < 80 OR calificacion IS NULL
   ORDER BY c.nombre";
$cursos = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis cursos inscritos</title>
  <link rel="stylesheet" href="../css/dashboard_estudiante.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

<!-- ───────── BARRA SUPERIOR ───────── -->
<header class="top-bar">
  <!-- Logo -->
  <div class="top-bar-left">
    <a href="dashboard_estudiante.php" class="logo-link">
      <img src="../images/logo_nuevo_blanco.png" class="logo" alt="Logo">
    </a>
  </div>

  <!-- Usuario + Hamburguesa -->
  <div class="top-bar-right">
    <img src="<?= htmlspecialchars($fotoURL) ?>" class="avatar" alt="Perfil">
    <span class="username"><?= htmlspecialchars($_SESSION['nombre']) ?></span>

    <!-- Checkbox que controla el menú -->
    <input type="checkbox" id="toggleMenu" class="toggle-menu">
    <label for="toggleMenu" class="hamburger"><i class="fa fa-bars"></i></label>

    <!-- Menú deslizable -->
    <nav class="slide-menu">
      <ul>
        <li class="menu-header">
          <img src="<?= htmlspecialchars($fotoURL) ?>" class="avatar-sm" alt="Avatar">
          <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
        </li>

        <!-- NUEVA opción Home -->
        <li><a href="dashboard_estudiante.php"><i class="fa fa-home"></i> Home</a></li>

        <!-- Enlace a esta misma página (facultativo) -->
        <li><a href="cursos_inscritos.php"><i class="fa fa-graduation-cap"></i> Mis Cursos</a></li>

        <li class="divider"></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
      </ul>
    </nav>
  </div>
</header>
<!-- ─────── /BARRA SUPERIOR ─────── -->

<!-- No hay banner -->

<nav class="breadcrumb">
  <ul>
    <li><a href="dashboard_estudiante.php">Inicio</a></li>
    <li>Mis cursos</li>
  </ul>
</nav>

<main class="layout-container" style="grid-template-columns:1fr;">
  <section class="main-content">
    <h2>Mis cursos inscritos</h2>

    <?php if ($cursos && $cursos->num_rows > 0): ?>
      <div class="course-grid">
        <?php while ($curso = $cursos->fetch_assoc()): ?>
          <?php
            $cal = $curso['calificacion'];
            $btn = is_null($cal) ? "Acceder" : "Repetir";
          ?>
          <div class="course-card enrolled">
            <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
            <p><?= htmlspecialchars($curso['descripcion']) ?></p>
            <a href="course_content.php?course_id=<?= $curso['id'] ?>" class="btn-acceder"><?= $btn ?></a>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p>No tienes cursos pendientes para repetir o completar.</p>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
