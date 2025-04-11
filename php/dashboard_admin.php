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
  <!-- (Opcional) Font Awesome para íconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/dashboard_admin.css"> <!-- Asegúrate de apuntar bien a tu CSS -->
</head>
<body>

  <!-- Banner completo -->
  <div class="banner">
    <img src="../images/banner.png" alt="Banner Talento+">
  </div>

  <!-- Encabezado con saludo centrado y fila de botones -->
  <header class="top-header">
    <!-- Mensaje de bienvenida -->
    <h1>Bienvenido, Administrador <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    
    <!-- Fila de botones (white-box style) -->
    <ul class="nav-bar">
      <li><a href="register_user.php" class="white-box-btn"><i class="fa fa-users"></i> Gestionar Usuarios</a></li>
      <li><a href="reports.php" class="white-box-btn"><i class="fa fa-bar-chart"></i> Reportes</a></li>
      <li><a href="system_settings.php" class="white-box-btn"><i class="fa fa-cog"></i> Configuración</a></li>
      <li><a href="logout.php" class="white-box-btn"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
    </ul>
  </header>
  
  <!-- Contenido principal -->
  <main class="main-content">
    <h2>Escritorio Administrador</h2>
    <p>Aquí puedes gestionar usuarios, ver reportes y configurar el sistema.</p>
  </main>

</body>
</html>
