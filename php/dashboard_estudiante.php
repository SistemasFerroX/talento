<?php
// dashboard_estudiante.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$student_id = $_SESSION['user_id'];
$empresa    = $mysqli->real_escape_string($_SESSION['empresa']);

/* foto de perfil ---------------------------------------------------- */
$fotoFile = $_SESSION['foto'] ?? '';
$fotoDisk = __DIR__.'/../uploads/'.$fotoFile;
$fotoURL  = ($fotoFile && file_exists($fotoDisk))
            ? '../uploads/'.rawurlencode($fotoFile)
            : '../images/default-avatar.png';

/* cursos disponibles (no inscritos) --------------------------------- */
$sql = "
  SELECT id,nombre,descripcion,portada
    FROM courses
   WHERE id NOT IN (SELECT course_id
                      FROM enrollments
                     WHERE user_id = $student_id)
     AND empresa = '$empresa'";
$cursos = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Estudiante</title>

  <link rel="stylesheet" href="../css/dashboard_estudiante.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ═══════════ BARRA SUPERIOR ═══════════ -->
<header class="top-bar">
  <!-- Logo -->
  <div class="top-bar-left">
    <a href="dashboard_estudiante.php" class="logo-link">
      <img src="../images/logo_final_superior_imagen_texto_blanco.png" class="logo" alt="Logo">
    </a>
  </div>

  <!-- Eslogan -->
  <div class="slogan">Avanza con propósito, crece con&nbsp;visión</div>

  <!-- Usuario -->
  <div class="top-bar-right">
    <img src="<?= htmlspecialchars($fotoURL) ?>" class="avatar" alt="Perfil">
    <span class="username"><?= htmlspecialchars($_SESSION['nombre']) ?></span>

    <input type="checkbox" id="toggleMenu" class="toggle-menu">
    <label for="toggleMenu" class="hamburger"><i class="fa-solid fa-bars"></i></label>

    <nav class="slide-menu">
      <ul>
        <li class="menu-header">
          <img src="<?= htmlspecialchars($fotoURL) ?>" class="avatar-sm" alt="Avatar">
          <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
        </li>

        <li><a href="perfil_estudiante.php"><i class="fa-regular fa-user"></i> Mi Perfil</a></li>
        <li><a href="cursos_inscritos.php"><i class="fa fa-graduation-cap" style="color:#000;"></i> Mis Cursos</a></li>
        <li><a href="cursos_realizados.php"><i class="fa-solid fa-clipboard-check"></i> Cursos Realizados</a></li>
        <li><a href="forum.php"><i class="fa fa-graduation-cap" style="color:#000;"></i> Foro</a></li>

        <li class="divider"></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
      </ul>
    </nav>
  </div>
</header>

<!-- ═══════════ BANNER (slider) ═══════════ -->
<div class="banner-slider">
  <img id="bannerImg" src="../images/RECURSOS_BANNER1.png" alt="Banner">
</div>

<!-- Breadcrumb (solo franja gris) -->
<nav class="breadcrumb"><ul><li>&nbsp;</li></ul></nav>

<!-- ═══════════ CONTENIDO ═══════════ -->
<main class="layout-container" style="grid-template-columns:1fr;">
  <div class="main-content">
    <section class="section-courses">
      <h2>Cursos Disponibles</h2>

      <?php if ($cursos && $cursos->num_rows): ?>
        <div class="course-grid">
          <?php while ($c = $cursos->fetch_assoc()): ?>
            <?php
              $cover = $c['portada']
                       ? "../uploads/covers/{$c['portada']}"
                       : "../images/placeholder.jpg";
            ?>
            <a href="course_detail.php?course_id=<?= $c['id'] ?>"
               class="course-thumb"
               style="background-image:url('<?= $cover ?>');">
               <span><?= htmlspecialchars($c['nombre']) ?></span>
            </a>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <p>No hay cursos disponibles para inscribirse.</p>
      <?php endif; ?>
    </section>
  </div>
</main>

<!-- Slider script (4 imágenes · 6 s) -->
<script>
const slides=["../images/RECURSOS_BANNER1.png",
              "../images/RECURSOS_BANNER2.png",
              "../images/RECURSOS_BANNER3.png",
              "../images/RECURSOS_BANNER4.png"];
let idx=0;
const img=document.getElementById("bannerImg");
setInterval(()=>{
  idx=(idx+1)%slides.length;
  img.style.opacity=0;
  setTimeout(()=>{img.src=slides[idx];img.style.opacity=1;},400);
},6000);
</script>
</body>
</html>
