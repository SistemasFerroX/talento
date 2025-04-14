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
    /* Sección de reportes */
    .reports-section {
        background-color: #fff;
        padding: 20px;
        margin: 20px auto;
        max-width: 800px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .reports-section h3 {
        text-align: center;
        color: #003366;
        margin-bottom: 20px;
    }
    .reports-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }
    .report-card {
        background-color: #f8f8f8;
        width: 250px;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        text-align: center;
    }
    .report-card h3 {
        margin-bottom: 10px;
        color: #003366;
    }
    .report-card p {
        margin-bottom: 15px;
        color: #555;
    }
    .report-link {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007BFF;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        transition: background 0.3s;
    }
    .report-link:hover {
        background-color: #0056b3;
    }
  </style>
</head>
<body>
  <!-- Banner completo -->
  <div class="banner">
    <img src="../images/banner.png" alt="Banner Talento+">
  </div>

  <!-- Encabezado con saludo y navegación -->
  <header class="top-header">
    <h1>Bienvenido, Administrador <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    <ul class="nav-bar">
      <li><a href="register_user.php" class="white-box-btn"><i class="fa fa-users"></i> Gestionar Usuarios</a></li>
      <li><a href="dashboard_admin.php" class="white-box-btn"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="system_settings.php" class="white-box-btn"><i class="fa fa-cog"></i> Configuración</a></li>
      <li><a href="logout.php" class="white-box-btn"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
    </ul>
  </header>
  
  <!-- Contenido principal -->
  <main class="main-content">
    <h2>Escritorio Administrador</h2>
    <p>Aquí puedes gestionar usuarios, revisar configuraciones y generar reportes.</p>
    
    <!-- Sección de Reportes que se muestra al hacer clic en el botón "Reportes" -->
    <!-- Puedes mostrar esta sección de forma modal o a pantalla completa según tu preferencia -->
    <section class="reports-section">
      <h3>Reportes Disponibles</h3>
      <div class="reports-grid">
        <!-- Reporte 1: Informe por Estudiante -->
        <div class="report-card">
          <h3>Informe por Estudiante</h3>
          <p>Ver un listado de estudiantes y generar un informe PDF con los cursos realizados, notas y fechas.</p>
          <a href="lista_estudiantes.php" class="report-link">Ver Informe</a>
        </div>
        <!-- Reporte 2: Informe por Curso -->
        <div class="report-card">
          <h3>Informe por Curso</h3>
          <p>Ver un listado de cursos y generar un informe PDF con los estudiantes inscritos, notas y fechas de realización.</p>
          <a href="lista_cursos.php" class="report-link">Ver Informe</a>
        </div>
        <!-- Reporte 3: Informe General por Rango de Fechas -->
        <div class="report-card">
          <h3>Informe General</h3>
          <p>Seleccionar un rango de fechas y generar un informe PDF de estudiantes que realizaron cursos en ese período.</p>
          <a href="informe_general_form.php" class="report-link">Ver Informe</a>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
