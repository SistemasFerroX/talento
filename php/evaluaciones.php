<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

// 1) Sólo plantillas activas
$tpls = $mysqli->query("
  SELECT id, title, banner
    FROM evaluation_templates
   WHERE is_active = 1
ORDER BY title
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Evaluaciones Disponibles</title>
  <link rel="stylesheet" href="../css/dashboard_estudiante.css">
  <style>
    .main-content { max-width:800px; margin:20px auto; }
    .templates-grid { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; }
    .template-card {
      width:220px; background:#fff; border:1px solid #ddd; border-radius:6px;
      overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);
      display:flex; flex-direction:column; text-align:center;
    }
    .template-card img { width:100%; height:100px; object-fit:cover; }
    .template-card h3 { margin:12px 0 8px; font-size:1.1em; color:#003366; }
    .template-card p { flex:1; padding:0 10px 10px; font-size:0.9em; color:#555; }
    .btn-evaluar {
      display:block; margin:0 10px 10px;
      padding:8px; background:#28a745; color:#fff; text-decoration:none;
      border-radius:4px; font-weight:bold;
    }
    .btn-evaluar:hover { background:#218838; }
  </style>
</head>
<body>

  <!-- Header Estudiante -->
  <header class="top-bar">
    <div class="top-bar-left">
      <img src="../images/logo.png" alt="Logo" class="logo">
      <span class="site-name">Plataforma de Cursos</span>
    </div>
    <div class="top-bar-right">
      <span class="username">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
      <a href="perfil_estudiante.php" class="profile-btn">Mi Perfil</a>
      <a href="forum.php" class="forum-btn">Foro</a>
      <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>
  </header>

  <!-- Banner principal -->
  <div class="banner">
    <img src="../images/talento1.png" alt="Banner Estudiante" style="width:100%;display:block">
  </div>

  <!-- Breadcrumb -->
  <nav class="breadcrumb" style="max-width:800px; margin:10px auto;">
    <ul style="list-style:none; padding:0; display:flex; gap:5px; font-size:0.9em;">
      <li><a href="dashboard_estudiante.php">Inicio</a></li>
      <li>›</li>
      <li>Evaluaciones</li>
    </ul>
  </nav>

  <!-- Contenido principal: lista de plantillas -->
  <div class="main-content">
    <h2>Evaluaciones Disponibles</h2>
    <p>Califica cada criterio de 1 a 5:<br>
       <strong>5</strong>: Cumple ✓✓✓✓✓<br>
       <strong>4</strong>: Casi Siempre ✓✓✓✓<br>
       <strong>3</strong>: Parcialmente ✓✓✓<br>
       <strong>2</strong>: Pocas Veces ✓✓<br>
       <strong>1</strong>: No Cumple ✗
    </p>

    <div class="templates-grid">
      <?php if ($tpls->num_rows): ?>
        <?php while ($tpl = $tpls->fetch_assoc()): ?>
          <div class="template-card">
            <?php if ($tpl['banner']): ?>
              <img src="../uploads/<?= htmlspecialchars($tpl['banner']) ?>" alt="">
            <?php endif; ?>
            <h3><?= htmlspecialchars($tpl['title']) ?></h3>
            <p>Evaluación <em><?= htmlspecialchars($tpl['title']) ?></em>.<br>
               Mide las competencias desde esta perspectiva.</p>
            <a href="evaluar.php?template_id=<?= $tpl['id'] ?>"
               class="btn-evaluar">Comenzar</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No hay evaluaciones activas en este momento.</p>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>
