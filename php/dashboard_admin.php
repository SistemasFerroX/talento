<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Administrador</title>
  <!-- Fuente para los íconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Enlace a tu archivo CSS personalizado -->
  <link rel="stylesheet" href="../css/dashboard_admin.css">
  <style>
    /* Estilos generales */
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        margin: 0;
        padding: 0;
    }
    .banner img {
        width: 100%;
        height: auto;
    }
    .top-header {
        text-align: center;
        padding: 20px;
        background-color: #003366;
        color: #fff;
    }
    .nav-bar {
        list-style: none;
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 20px 0;
        padding: 0;
    }
    .nav-bar li a {
        display: inline-block;
        padding: 10px 15px;
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
    .main-content {
        padding: 20px;
    }
    /* Sección Reportes */
    .reports-section {
        background-color: #fff;
        padding: 30px;
        margin: 30px auto;
        max-width: 900px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    .reports-section h3 {
        text-align: center;
        color: #003366;
        margin-bottom: 30px;
        font-size: 22px;
    }
    .reports-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 25px;
    }
    .report-card {
        background-color: #f8f8f8;
        width: 250px;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        text-align: center;
    }
    .report-card h3 {
        margin-bottom: 12px;
        color: #003366;
        font-size: 18px;
    }
    .report-card p {
        margin-bottom: 20px;
        color: #555;
        font-size: 14px;
    }
    .report-link {
        display: inline-block;
        padding: 12px 25px;
        background-color: #007BFF;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        transition: background 0.3s;
        font-size: 15px;
    }
    .report-link:hover {
        background-color: #0056b3;
    }
    .dashboard-info {
        text-align: center;
        margin-bottom: 30px;
    }
    .back-btn {
      display: inline-block;
      margin-top: 40px;
      padding: 12px 20px;
      background: #28a745;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s;
      font-size: 15px;
    }
    .back-btn:hover {
      background: #1e7e34;
    }
    .banner-profesor img {
      width: 100%;
      height: 120px;         /* O ajusta a 100px si lo quieres más pequeño */
      object-fit: cover;
}
  </style>
</head>
<body>
  <!-- Banner -->
  <div class="banner banner-profesor">
    <img src="../images/talento2.png" alt="Banner Talento+">
  </div>

  <!-- Encabezado y navegación -->
  <header class="top-header">
    <h1>Bienvenido, Administrador <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    <ul class="nav-bar">
      <li><a href="register_user.php"><i class="fa fa-users"></i> Gestionar Usuarios</a></li>
      <li><a href="dashboard_admin.php"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="system_settings.php"><i class="fa fa-cog"></i> Configuración</a></li>
      <!-- Botón de Foro en la barra de navegación -->
      <li><a href="forum.php"><i class="fa fa-comments"></i> Foro</a></li>
      <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
    </ul>
  </header>

  <!-- Contenido principal del Dashboard -->
  <main class="main-content">
    <div class="dashboard-info">
      <h2>Escritorio Administrador</h2>
      <p>Aquí puedes gestionar usuarios, revisar configuraciones y generar reportes.</p>
    </div>

    <!-- Sección de Reportes -->
    <section class="reports-section">
      <h3>Reportes Disponibles</h3>
      <div class="reports-grid">
        <!-- Informe por Estudiante -->
        <div class="report-card">
          <h3>Informe por Estudiante</h3>
          <p>Genera un PDF con la información de los cursos realizados por un estudiante.</p>
          <a href="lista_estudiantes.php" class="report-link">Ver Informe</a>
        </div>
        <!-- Informe por Curso -->
        <div class="report-card">
          <h3>Informe por Curso</h3>
          <p>Genera un PDF con el listado de estudiantes inscritos en un curso.</p>
          <a href="lista_cursos.php" class="report-link">Ver Informe</a>
        </div>
        <!-- Informe General por Rango de Fechas -->
        <div class="report-card">
          <h3>Informe General</h3>
          <p>Selecciona un rango de fechas y genera un informe PDF de inscripciones en ese período.</p>
          <a href="informe_general_form.php" class="report-link">Ver Informe</a>
        </div>
      </div>
    </section>

  </main>
</body>
</html>
