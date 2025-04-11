<?php
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'domain'   => '',  // O 'localhost' si lo prefieres
  'secure'   => false,  // false, porque usas HTTP, no HTTPS
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

// Verificar si el usuario está autenticado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'estudiante') {
    header("Location: ../login.html");
    exit;
}

require 'config.php'; // Conexión a la base de datos

$user_id = $_SESSION['user_id'];
// Obtener información del usuario (incluyendo el número de celular)
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $mysqli->query($query);
$user = $result->fetch_assoc();

// Procesar la subida de una nueva foto de perfil (la única modificación permitida)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/";
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName    = $_FILES['foto']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = $user_id . "_" . time() . "." . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $updateQuery = "UPDATE users SET foto = '$newFileName' WHERE id = $user_id";
                if ($mysqli->query($updateQuery)) {
                    $mensaje = "Foto de perfil actualizada exitosamente.";
                    // Recargar la información actualizada del usuario
                    $query = "SELECT * FROM users WHERE id = $user_id";
                    $result = $mysqli->query($query);
                    $user = $result->fetch_assoc();
                } else {
                    $mensaje = "Error al actualizar la foto: " . $mysqli->error;
                }
            } else {
                $mensaje = "Error al mover el archivo a la carpeta de subida.";
            }
        } else {
            $mensaje = "Extensión de archivo no permitida. Usa JPG, JPEG, PNG o GIF.";
        }
    } else {
        $mensaje = "No se seleccionó un archivo o hubo un error en la subida.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Perfil - Estudiante</title>
  <link rel="stylesheet" href="../css/perfil.css">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    header { margin-bottom: 20px; }
    header nav a { margin-right: 15px; text-decoration: none; color: #007BFF; }
    .mensaje { padding: 10px; background: #e1f3e5; border: 1px solid #b6dec2; color: #205628; margin-bottom: 15px; border-radius: 4px; }
    .profile-container { display: flex; align-items: center; margin-bottom: 30px; }
    .profile-photo img { max-width: 150px; border-radius: 8px; margin-right: 20px; }
    .profile-info p { margin: 5px 0; font-size: 16px; }
    .update-photo { margin-top: 30px; }
    .update-photo .form-group { margin-bottom: 15px; }
    .update-photo label { display: block; margin-bottom: 5px; font-weight: bold; }
    .update-photo input[type="file"] { width: 100%; }
    .update-photo button { padding: 10px 15px; background: #007BFF; color: #fff; border: none; cursor: pointer; border-radius: 4px; }
    .update-photo button:hover { background: #0056b3; }
  </style>
</head>
<body>
  <header>
    <h1>Mi Perfil</h1>
    <nav>
      <a href="dashboard_estudiante.php">Inicio</a>
      <a href="logout.php">Cerrar Sesión</a>
    </nav>
  </header>
  
  <main>
    <?php if(isset($mensaje)): ?>
      <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <div class="profile-container">
      <div class="profile-photo">
        <?php if(!empty($user['foto'])): ?>
          <img src="../uploads/<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto de Perfil">
        <?php else: ?>
          <img src="../images/default-profile.png" alt="Foto de Perfil">
        <?php endif; ?>
      </div>
      <div class="profile-info">
        <p><strong>Cédula:</strong> <?php echo htmlspecialchars($user['cedula']); ?></p>
        <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($user['nombre_completo'] . " " . $user['apellidos']); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($user['correo']); ?></p>
        <p><strong>Número de Celular:</strong> <?php echo htmlspecialchars($user['celular']); ?></p>
        <p><strong>Rol:</strong> <?php echo htmlspecialchars($user['rol']); ?></p>
        <p><strong>Empresa:</strong> <?php echo htmlspecialchars($user['empresa']); ?></p>
        <p><strong>Sub Empresa:</strong> <?php echo htmlspecialchars($user['sub_empresa']); ?></p>
        <p><strong>Sub Sub Empresa:</strong> <?php echo htmlspecialchars($user['sub_sub_empresa']); ?></p>
        <p><strong>Cargo:</strong> <?php echo htmlspecialchars($user['cargo']); ?></p>
        <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($user['fecha_creacion']); ?></p>
      </div>
    </div>
    
    <!-- Formulario para actualizar solo la foto de perfil -->
    <section class="update-photo">
      <h2>Actualizar Foto de Perfil</h2>
      <form action="perfil_estudiante.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="foto">Selecciona una foto:</label>
          <input type="file" name="foto" id="foto" accept="image/*" required>
        </div>
        <button type="submit">Actualizar Foto</button>
      </form>
    </section>
  </main>
</body>
</html>
