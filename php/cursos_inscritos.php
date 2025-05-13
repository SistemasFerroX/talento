<?php
// cursos_inscritos.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol']!=='estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$student_id = (int)$_SESSION['user_id'];
$empresa    = $mysqli->real_escape_string($_SESSION['empresa']);

// Foto para el menú
$fotoFile = $_SESSION['foto'] ?? '';
$fotoDisk = __DIR__ . '/../uploads/' . $fotoFile;
$fotoURL  = ($fotoFile && file_exists($fotoDisk))
         ? '../uploads/' . rawurlencode($fotoFile)
         : '../images/default-avatar.png';

// Recupero los cursos pendientes
$sql = "
  SELECT c.id, c.nombre,
         (SELECT MAX(g.calificacion)
            FROM grades g
           WHERE g.course_id = c.id
             AND g.user_id   = $student_id
         ) AS cal
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
   WHERE e.user_id = $student_id
     AND c.empresa = '$empresa'
   HAVING cal < 80 OR cal IS NULL
   ORDER BY c.nombre
";
$cursos = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis cursos inscritos</title>
  <link rel="stylesheet" href="../css/cursos_inscritos.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- BARRA SUPERIOR -->
  <header class="top-bar">
    <div class="top-bar-left">
      <a href="dashboard_estudiante.php">
        <img src="../images/logo_final_superior_imagen_texto_blanco.png" class="logo" alt="Talento+">
      </a>
    </div>
    <div class="slogan">Impulsa tu talento, transforma tu futuro</div>
    <div class="top-bar-right">
      <img src="<?= $fotoURL ?>" class="avatar" alt="">
      <span class="username"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
      <input type="checkbox" id="toggleMenu" class="toggle-menu">
      <label for="toggleMenu" class="hamburger"><i class="fa fa-bars"></i></label>
      <nav class="slide-menu">
        <ul>
          <li class="menu-header">
            <img src="<?= $fotoURL ?>" class="avatar-sm" alt="">
            <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
          </li>
          <li><a href="dashboard_estudiante.php"><i class="fa fa-home"></i> Home</a></li>
          <li><a href="cursos_inscritos.php"><i class="fa fa-graduation-cap"></i> Mis Cursos</a></li>
          <li class="divider"></li>
          <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- SIN BÚSQUEDA NI CATEGORÍAS -->

  <main class="layout-container">

    <!-- CONTENEDOR VERDE: Título y lista de cursos -->
    <section class="box-green">
      <h2>Mis cursos inscritos</h2>
      <?php if ($cursos && $cursos->num_rows > 0): ?>
        <ul class="course-list">
          <?php while ($c = $cursos->fetch_assoc()): 
            $btn = is_null($c['cal']) ? 'Empezar Curso' : 'Repetir Curso';
          ?>
            <li>
              <span><?= htmlspecialchars($c['nombre']) ?></span>
              <a href="course_content.php?course_id=<?= $c['id'] ?>" class="btn-curso"><?= $btn ?></a>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No tienes cursos pendientes.</p>
      <?php endif; ?>
    </section>

    <!-- CONTENEDOR AMARILLO: Banner central -->
    <section class="box-yellow">
      <div class="banner">
        <img src="../images/banner_mis_cursos.png" alt="Impulsa tu futuro hoy">
      </div>
    </section>

  </main>

</body>
</html>
