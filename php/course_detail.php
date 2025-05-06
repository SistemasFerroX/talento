<?php
/* ----------------  SEGURIDAD ---------------- */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../login.html");   exit;
}
require 'config.php';

/* ----------------  VALIDACIONES -------------- */
if (!isset($_GET['course_id'])) { exit('ID de curso no especificado.'); }
$course_id        = (int) $_GET['course_id'];
$user_id          = $_SESSION['user_id'];
$student_empresa  = $mysqli->real_escape_string($_SESSION['empresa']);

/* ----------------  DATOS DEL CURSO ----------- */
$qCourse = $mysqli->query("SELECT * FROM courses WHERE id = $course_id");
if (!$qCourse || !$qCourse->num_rows) { exit('El curso no existe.'); }
$course = $qCourse->fetch_assoc();

/* ← ¿el curso es de la misma empresa? */
if ($course['empresa'] !== $student_empresa) { exit('No tienes permiso.'); }

/* ----------------  MATERIALES ---------------- */
$documents = $videos = [];
$mat = $mysqli->query("SELECT * FROM course_materials WHERE course_id = $course_id");
if ($mat) {
    while ($row = $mat->fetch_assoc()) {
        ($row['material_type'] === 'document') ? $documents[] = $row['material_value']
                                               : $videos[]    = $row['material_value'];
    }
}

/* avatar usuario (para la barra) */
$fotoFile = $_SESSION['foto'] ?? '';
$fotoDisk = __DIR__ . '/../uploads/' . $fotoFile;
$fotoURL  = ($fotoFile && file_exists($fotoDisk)) ? '../uploads/' . rawurlencode($fotoFile)
                                                  : '../images/default-avatar.png';

/* url del primer video (YouTube) ------------- */
$videoEmbed = count($videos)
              ? preg_replace('#watch\?v=#','embed/',$videos[0])   /* youtube → embed */
              : 'https://www.youtube.com/embed/Cj5qd2Yolws?si=C-tasjg3PhQD8BcD';      /* placeholder */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($course['nombre']); ?> | Curso</title>

  <link rel="stylesheet" href="../css/course_detail.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ========= BARRA SUPERIOR ========= -->
<header class="top-bar">
  <div class="top-bar-left">
      <a href="dashboard_estudiante.php"><img src="../images/logo_nuevo_blanco.png" class="logo" alt="Logo"></a>
  </div>

   <!-- eslogan centrado -->
  <div class="slogan">Avanza con propósito, crece con&nbsp;visión</div>

  <div class="top-bar-right">
      <img src="<?php echo htmlspecialchars($fotoURL); ?>" class="avatar" alt="Perfil">
      <span class="username"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>

      <input type="checkbox" id="toggleMenu" class="toggle-menu">
      <label for="toggleMenu" class="hamburger"><i class="fa fa-bars"></i></label>

      <nav class="slide-menu">
        <ul>
          <li class="menu-header">
            <img src="<?php echo htmlspecialchars($fotoURL); ?>" class="avatar-sm" alt="">
            <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
          </li>

          <li><a href="perfil_estudiante.php"><i class="fa-regular fa-user"></i> Mi Perfil</a></li>
          <li><a href="dashboard_estudiante.php"><i class="fa fa-home"></i> Home</a></li>
          <li><a href="cursos_inscritos.php"><i class="fa fa-graduation-cap" style="color:#000;"></i> Mis Cursos</a></li>
          <li><a href="cursos_realizados.php"><i class="fa-solid fa-clipboard-check"></i> Cursos Realizados</a></li>

          <li class="divider"></li>
          <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
      </nav>
  </div>
</header>

<!-- ========= CONTENIDO ========= -->
<main class="wrapper">
  <a class="back-link" href="dashboard_estudiante.php"><i class="fa fa-angle-left"></i> Volver</a>

  <!-- HERO (2 columnas) -->
  <section class="hero">
      <div class="hero-text">
          <h1><?php echo htmlspecialchars($course['nombre']); ?></h1>
          <p class="short-desc">
              <?php echo nl2br(htmlspecialchars($course['descripcion'])); ?>
          </p>
          <p class="cta-intro">¡Impulsa tu desarrollo profesional!</p>

          <a href="enroll.php?course_id=<?php echo $course_id; ?>" class="cta-btn">
            Ver Curso
          </a>
      </div>

      <div class="hero-media">
          <iframe src="<?php echo htmlspecialchars($videoEmbed); ?>"
                  title="Video de apoyo"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen></iframe>
      </div>
  </section>

  <!-- SOBRE EL CURSO -->
  <section class="about-course">
      <h2>Sobre el curso</h2>

      <article class="course-description">
          <?php echo nl2br(htmlspecialchars($course['descripcion_larga'] ?? $course['descripcion'])); ?>
      </article>

      <!-- Documentos -->
      <div class="materials">
          <?php if ($documents) : ?>
              <h3>Documentos de apoyo</h3>
              <ul>
                  <?php foreach ($documents as $doc): ?>
                      <li><a href="../documents/<?php echo htmlspecialchars($doc); ?>" target="_blank">
                          <i class="fa fa-file-pdf"></i> <?php echo htmlspecialchars($doc); ?></a>
                      </li>
                  <?php endforeach; ?>
              </ul>
          <?php endif; ?>

          <?php if (count($videos) > 1) : ?>
              <h3>Videos adicionales</h3>
              <ul>
                  <?php foreach (array_slice($videos,1) as $vid): ?>
                      <li><a href="<?php echo htmlspecialchars($vid); ?>" target="_blank">
                          <i class="fa fa-video"></i> <?php echo htmlspecialchars($vid); ?></a>
                      </li>
                  <?php endforeach; ?>
              </ul>
          <?php endif; ?>
      </div>
  </section>
</main>
</body>
</html>
