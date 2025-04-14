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
  <title>Reportes - Administrador</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/dashboard_admin.css"> <!-- Ajusta la ruta según necesites -->
  <style>
    /* Estilos específicos para la página de reportes */
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
      text-align: center;
    }
    .reports-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
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
    .report-btn {
      display: inline-block;
      padding: 10px 15px;
      background-color: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s;
    }
    .report-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <!-- Banner completo -->
  <div class="banner">
    <img src="../images/banner.png" alt="Banner Talento+">
  </div>

  <!-- Encabezado con navegación -->
  <header class="top-header">
    <h1>Bienvenido, Administrador <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    <ul class="nav-bar">
      <li><a href="register_user.php" class="white-box-btn"><i class="fa fa-users"></i> Gestionar Usuarios</a></li>
      <li><a href="reports.php" class="white-box-btn"><i class="fa fa-bar-chart"></i> Reportes</a></li>
      <li><a href="system_settings.php" class="white-box-btn"><i class="fa fa-cog"></i> Configuración</a></li>
      <li><a href="logout.php" class="white-box-btn"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
    </ul>
  </header>

  <main class="main-content">
    <h2>Reportes Disponibles</h2>
    <p>Selecciona un tipo de informe para generar en PDF.</p>
    
    <div class="reports-grid">
      <!-- Card 1: Informe por Estudiante -->
      <div class="report-card">
        <h3>Informe por Estudiante</h3>
        <p>Genera un PDF con los cursos que ha realizado cada estudiante, sus calificaciones y fechas.</p>
        <a href="lista_estudiantes.php" class="report-btn">Ver Lista de Estudiantes</a>
      </div>

      <!-- Card 2: Informe por Curso -->
      <div class="report-card">
        <h3>Informe por Curso</h3>
        <p>Selecciona un curso y genera un PDF con los estudiantes inscritos, sus notas y fechas.</p>
        <a href="lista_cursos.php" class="report-btn">Ver Lista de Cursos</a>
      </div>

      <!-- Card 3: Informe General por Fechas -->
      <div class="report-card">
        <h3>Informe General</h3>
        <p>Selecciona un rango de fechas para generar un PDF con estudiantes y cursos realizados.</p>
        <a href="informe_general.php" class="report-btn">Generar Informe General</a>
      </div>
    </div>
  </main>
</body>
</html>
