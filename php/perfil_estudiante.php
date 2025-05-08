<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol']!=='estudiante') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

// 1) Cargo datos del usuario
$user_id = (int)$_SESSION['user_id'];
$res     = $mysqli->query("SELECT * FROM users WHERE id=$user_id");
$user    = $res->fetch_assoc();

// 2) Subida de foto
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['foto'])) {
    $f = $_FILES['foto'];
    if ($f['error']===UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','gif'])) {
            $nuevo = "{$user_id}_" . time() . ".{$ext}";
            $dest  = __DIR__ . "/../uploads/$nuevo";
            if (move_uploaded_file($f['tmp_name'],$dest)) {
                $mysqli->query("UPDATE users SET foto='$nuevo' WHERE id=$user_id");
                $user['foto'] = $nuevo;
            }
        }
    }
}

// 3) Actualizo correo y celular
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $correo  = $mysqli->real_escape_string($_POST['correo']);
    $celular = $mysqli->real_escape_string($_POST['celular']);
    $mysqli->query("
      UPDATE users
         SET correo='$correo', celular='$celular'
       WHERE id=$user_id
    ");
    $r = $mysqli->query("SELECT * FROM users WHERE id=$user_id");
    $user = $r->fetch_assoc();
}

// URL de la foto
$fotoURL = (!empty($user['foto']) && file_exists(__DIR__."/../uploads/{$user['foto']}"))
         ? "../uploads/{$user['foto']}"
         : "../images/default-avatar.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Perfil — Estudiante</title>
  <link rel="stylesheet" href="../css/perfil_estudiante.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">
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
      <input type="checkbox" id="toggleMenu" class="toggle-menu">
      <label for="toggleMenu" class="hamburger"><i class="fa-solid fa-bars"></i></label>
      <nav class="slide-menu">
        <ul>
          <li class="menu-header">
            <img src="<?=htmlspecialchars($fotoURL)?>" class="avatar-sm" alt="">
            <strong><?=htmlspecialchars($user['nombre_completo'].' '.$user['apellidos'])?></strong>
          </li>
          <li><a href="perfil_estudiante.php"><i class="fa-regular fa-user"></i>Mi Perfil</a></li>
          <li><a href="cursos_inscritos.php"><i class="fa-solid fa-graduation-cap"></i>Mis Cursos</a></li>
          <li><a href="cursos_realizados.php"><i class="fa-solid fa-clipboard-check"></i>Cursos Realizados</a></li>
          <li><a href="forum.php"><i class="fa-regular fa-comments"></i>Foro</a></li>
          <li class="divider"></li>
          <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Cerrar Sesión</a></li>
        </ul>
      </nav>
      <img src="<?=htmlspecialchars($fotoURL)?>" class="avatar" alt="Perfil">
      <span class="username"><?=htmlspecialchars($user['nombre_completo'].' '.$user['apellidos'])?></span>
    </div>
  </header>

  <!-- FRANJA MEDIA -->
  <div class="banner-top"></div>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="profile-content">

    <!-- ROJO: FOTO + NOMBRE -->
    <div class="profile-avatar">
      <form method="POST" enctype="multipart/form-data" class="avatar-wrapper">
        <img src="<?=htmlspecialchars($fotoURL)?>" alt="Avatar">
        <input type="file" name="foto" onchange="this.form.submit()">
        <div class="camera-icon"><i class="fa-solid fa-camera"></i></div>
      </form>
      <h1><?=htmlspecialchars($user['nombre_completo'].' '.$user['apellidos'])?></h1>
    </div>

    <!-- VERDE: FORMULARIO DE DATOS -->
    <div class="profile-info">
      <form method="POST" class="data-form">
        <input type="hidden" name="update_profile" value="1">
        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo"
               value="<?=htmlspecialchars($user['correo'])?>" required>

        <label for="celular">Celular</label>
        <input type="text" id="celular" name="celular"
               value="<?=htmlspecialchars($user['celular'])?>" required>

        <label for="cedula">Cédula</label>
        <input type="text" id="cedula"
               value="<?=htmlspecialchars($user['cedula'])?>" readonly>

        <label for="rol">Rol</label>
        <input type="text" id="rol"
               value="<?=htmlspecialchars($user['rol'])?>" readonly>

        <label for="empresa">Empresa</label>
        <input type="text" id="empresa"
               value="<?=htmlspecialchars($user['empresa'])?>" readonly>

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
