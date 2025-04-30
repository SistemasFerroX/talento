<?php
// dashboard_admin.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}
require __DIR__ . '/config.php';

// traemos las plantillas de evaluación y su estado
$tpls = $mysqli->query("
  SELECT id, title, is_active
    FROM evaluation_templates
   ORDER BY title
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Administrador</title>
  <!-- Font-Awesome para íconos -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Tu CSS personalizado -->
  <link rel="stylesheet" href="../css/dashboard_admin.css">
  <style>
    /* Banner */
    .banner img { width:100%; height:auto; display:block; }
    /* Encabezado */
    .top-header { background:#003366; color:#fff; text-align:center; padding:20px 0; }
    .nav-bar { list-style:none; display:flex; justify-content:center; gap:15px; margin:15px 0; padding:0; }
    .nav-bar li a {
      display:inline-block; padding:10px 15px;
      background:#fff; color:#003366; border:1px solid #003366;
      border-radius:4px; text-decoration:none; font-weight:bold;
      transition:background .3s,color .3s;
    }
    .nav-bar li a:hover { background:#003366; color:#fff; }
    /* Contenido principal */
    .main-content { padding:20px; max-width:960px; margin:0 auto; }
    .dashboard-info { text-align:center; margin-bottom:30px; }
    .dashboard-info h2 { color:#003366; margin-bottom:10px; }
    /* Reportes clásicos */
    .reports-section { background:#fff; padding:30px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:40px; }
    .reports-section h3 { text-align:center; color:#003366; margin-bottom:25px; font-size:22px; }
    .reports-grid { display:flex; flex-wrap:wrap; gap:25px; justify-content:center; }
    .report-card { background:#f8f8f8; width:260px; padding:20px; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,0.1); text-align:center; }
    .report-card h3 { color:#003366; margin-bottom:12px; font-size:18px; }
    .report-card p { color:#555; font-size:14px; margin-bottom:20px; line-height:1.4; }
    .report-link { display:inline-block; padding:10px 18px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; font-size:14px; transition:background .3s; }
    .report-link:hover { background:#0056b3; }

    /* Nueva sección de activación */
    .eval-list { list-style:none; padding:0; margin:0 auto; max-width:600px; }
    .eval-list li {
      display:flex; align-items:center; gap:10px;
      padding:8px 0; border-bottom:1px solid #eee;
    }
    .tpl-title { flex:1; font-weight:500; }
    .btn-small {
      padding:4px 8px; font-size:.85em; border-radius:4px;
      color:#fff; text-decoration:none; transition:opacity .2s;
    }
    .btn-activate   { background:#28a745; }
    .btn-deactivate { background:#dc3545; }
    .btn-view       { background:#007bff; }
    .btn-small:hover { opacity:.85; }
  </style>
</head>
<body>
  <!-- Banner -->
  <div class="banner">
    <img src="../images/talento2.png" alt="Banner Talento+">
  </div>

  <!-- Header y navegación -->
  <header class="top-header">
    <h1>Bienvenido, Administrador <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
    <ul class="nav-bar">
      <li><a href="register_user.php"><i class="fa fa-users"></i> Gestionar Usuarios</a></li>
      <li><a href="dashboard_admin.php"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="system_settings.php"><i class="fa fa-cog"></i> Configuración</a></li>
      <li><a href="forum.php"><i class="fa fa-comments"></i> Foro</a></li>
      <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
    </ul>
  </header>

  <!-- Contenido principal -->
  <main class="main-content">
    <div class="dashboard-info">
      <h2>Escritorio Administrador</h2>
      <p>Aquí puedes gestionar usuarios, revisar configuraciones y generar reportes.</p>
    </div>

    <!-- Tus reportes actuales -->
    <section class="reports-section">
      <h3>Reportes Disponibles</h3>
      <div class="reports-grid">
        <!-- Informe por Estudiante -->
        <div class="report-card">
          <h3>Informe por Estudiante</h3>
          <p>Genera un PDF con los cursos realizados por un estudiante.</p>
          <a href="lista_estudiantes.php" class="report-link">
            <i class="fa fa-user"></i> Lista de Estudiantes
          </a>
        </div>
        <!-- Informe por Curso -->
        <div class="report-card">
          <h3>Informe por Curso</h3>
          <p>Listado de estudiantes inscritos en un curso.</p>
          <a href="lista_cursos.php" class="report-link">
            <i class="fa fa-book"></i> Lista de Cursos
          </a>
        </div>
        <!-- Informe General -->
        <div class="report-card">
          <h3>Informe General</h3>
          <p>Selecciona un rango de fechas y genera un PDF de inscripciones.</p>
          <a href="informe_general_form.php" class="report-link">
            <i class="fa fa-calendar"></i> Informe General
          </a>
        </div>
        <!-- Informe de Evaluaciones (acceso a lista de estudiantes) -->
        <div class="report-card">
          <h3>Informe de Evaluaciones</h3>
          <p>Selecciona un estudiante para ver y exportar sus evaluaciones en PDF.</p>
          <a href="lista_estudiantes_eval.php" class="report-link">
            <i class="fa fa-bar-chart"></i> Lista de Estudiantes
          </a>
        </div>
      </div>
    </section>

    <!-- Nueva sección: activar / desactivar evaluaciones -->
    <section class="reports-section">
      <h3>Activar / Desactivar Evaluaciones</h3>
      <?php if ($tpls->num_rows): ?>
        <ul class="eval-list">
          <?php while($t = $tpls->fetch_assoc()): ?>
          <li>
            <span class="tpl-title"><?= htmlspecialchars($t['title']) ?></span>

            <?php if ($t['is_active']): ?>
            <a href="toggle_evaluation.php?template_id=<?= $t['id'] ?>"
               class="btn-small btn-deactivate">
              Desactivar
            </a>
            <?php else: ?>
            <a href="toggle_evaluation.php?template_id=<?= $t['id'] ?>"
               class="btn-small btn-activate">
              Activar
            </a>
            <?php endif ?>

          </li>
          <?php endwhile ?>
        </ul>
      <?php else: ?>
        <p style="text-align:center;">No hay plantillas de evaluación registradas.</p>
      <?php endif ?>
    </section>
  </main>
</body>
</html>
