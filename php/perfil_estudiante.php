<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol']!=='estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

// Cargo datos del usuario
$user_id = (int)$_SESSION['user_id'];
$res     = $mysqli->query("
    SELECT cedula,
           nombre_completo,
           apellidos,
           correo,
           celular,
           rol,
           empresa
      FROM users
     WHERE id = $user_id
     LIMIT 1
");
$user = $res->fetch_assoc();

// Procesar actualización de correo y celular
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $correo  = $mysqli->real_escape_string($_POST['correo']);
    $celular = $mysqli->real_escape_string($_POST['celular']);
    $mysqli->query("
      UPDATE users
         SET correo  = '$correo',
             celular = '$celular'
       WHERE id = $user_id
    ");
    // recargo los nuevos valores
    $r = $mysqli->query("SELECT correo, celular FROM users WHERE id = $user_id");
    $data = $r->fetch_assoc();
    $user['correo']  = $data['correo'];
    $user['celular'] = $data['celular'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Perfil — Estudiante</title>
  <link rel="stylesheet" href="../css/perfil_estudiante.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- BARRA SUPERIOR -->
  <header class="top-bar">
    <div class="top-bar-left">
      <a href="dashboard_estudiante.php">
        <img src="../images/logo_final_superior_imagen_texto_blanco.png"
             class="logo" alt="Talento+">
      </a>
    </div>
    <div class="slogan">Impulsa tu talento, transforma tu futuro</div>
    <div class="top-bar-right">
      <input type="checkbox" id="toggleMenu" class="toggle-menu">
      <label for="toggleMenu" class="hamburger">
        <i class="fa-solid fa-bars"></i>
      </label>
      <nav class="slide-menu">
        <ul>
          <li class="menu-header">
            <!-- Eliminado <img> -->
            <strong>
              <?= htmlspecialchars($user['nombre_completo'] . ' ' . $user['apellidos']) ?>
            </strong>
          </li>
          <li><a href="perfil_estudiante.php"><i class="fa-regular fa-user"></i> Mi Perfil</a></li>
          <li><a href="cursos_inscritos.php"><i class="fa-solid fa-graduation-cap"></i> Mis Cursos</a></li>
          <li><a href="cursos_realizados.php"><i class="fa-solid fa-clipboard-check"></i> Cursos Realizados</a></li>
          <li><a href="forum.php"><i class="fa-regular fa-comments"></i> Foro</a></li>
          <li class="divider"></li>
          <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
      </nav>
      <!-- Eliminado <img class="avatar"> -->
      <span class="username">
        <?= htmlspecialchars($user['nombre_completo'] . ' ' . $user['apellidos']) ?>
      </span>
    </div>
  </header>

  <!-- FRANJA MEDIA -->
  <div class="banner-top"></div>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="profile-content">

    <!-- ROJO: NOMBRE SOLAMENTE -->
    <div class="profile-avatar">
      <h1><?= htmlspecialchars($user['nombre_completo'] . ' ' . $user['apellidos']) ?></h1>
    </div>

    <!-- VERDE: FORMULARIO DE DATOS -->
    <div class="profile-info">
      <form method="POST" class="data-form">
        <input type="hidden" name="update_profile" value="1">

        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo"
               value="<?= htmlspecialchars($user['correo']) ?>" required>

        <label for="celular">Celular</label>
        <input type="text" id="celular" name="celular"
               value="<?= htmlspecialchars($user['celular']) ?>" required>

        <label for="cedula">Cédula</label>
        <input type="text" id="cedula"
               value="<?= htmlspecialchars($user['cedula']) ?>" readonly>

        <label for="rol">Rol</label>
        <input type="text" id="rol"
               value="<?= htmlspecialchars($user['rol']) ?>" readonly>

        <label for="empresa">Empresa</label>
        <input type="text" id="empresa"
               value="<?= htmlspecialchars($user['empresa']) ?>" readonly>

        <button type="submit">Actualizar Perfil</button>
      </form>
    </div>

    <!-- AMARILLO: BANNER A LA DERECHA -->
    <div class="profile-banner">
      <img src="../images/banner_mis_cursos.png" alt="Banner Mis Cursos">
    </div>

  </main>

</body>
</html>
