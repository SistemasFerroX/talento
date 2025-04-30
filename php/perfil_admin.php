<?php
session_start();

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

require 'config.php';

$admin_id = $_SESSION['user_id'];
$message = "";
$photoMessage = "";

// Procesar la actualización de la foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto']) && !empty($_FILES['foto']['name'])) {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $tmpPath = $_FILES['foto']['tmp_name'];
        $originalName = basename($_FILES['foto']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowedExtensions)) {
            $newFileName = $admin_id . "_" . time() . "." . $ext;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($tmpPath, $destPath)) {
                // Actualizar el campo foto en la base de datos
                $stmt = $mysqli->prepare("UPDATE users SET foto = ? WHERE id = ?");
                $stmt->bind_param("si", $newFileName, $admin_id);
                if ($stmt->execute()) {
                    $photoMessage = "Foto de perfil actualizada exitosamente.";
                    // Actualizamos la variable de sesión en caso de que la uses en otros lugares
                    $_SESSION['foto'] = $newFileName;
                } else {
                    $photoMessage = "Error al actualizar foto: " . $mysqli->error;
                }
                $stmt->close();
            } else {
                $photoMessage = "Error al mover el archivo a la carpeta de subida.";
            }
        } else {
            $photoMessage = "Extensión no permitida. Usa JPG, JPEG, PNG o GIF.";
        }
    } else {
        $photoMessage = "No se pudo subir la imagen. Error en la subida.";
    }
}

// Procesar la actualización de los datos personales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    // Escapar y recoger los campos
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $apellidos = $mysqli->real_escape_string($_POST['apellidos']);
    $correo = $mysqli->real_escape_string($_POST['correo']);
    $cargo = $mysqli->real_escape_string($_POST['cargo']);
    
    $stmt = $mysqli->prepare("UPDATE users SET nombre_completo = ?, apellidos = ?, correo = ?, cargo = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nombre, $apellidos, $correo, $cargo, $admin_id);
    if ($stmt->execute()) {
        $message = "Datos actualizados correctamente.";
        // Actualizamos la sesión, en caso de mostrar el nombre actualizado en el dashboard, por ejemplo.
        $_SESSION['nombre'] = $nombre;
    } else {
        $message = "Error al actualizar datos: " . $mysqli->error;
    }
    $stmt->close();
}

// Recuperar la información actualizada del admin para mostrarla
$query = "SELECT * FROM users WHERE id = $admin_id LIMIT 1";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    $adminData = $result->fetch_assoc();
} else {
    die("Error al obtener la información del administrador.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil Administrador</title>
  <link rel="stylesheet" href="../css/perfil_admin.css">
  <style>
    /* Estilos básicos para el perfil del admin */
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      padding: 20px 30px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #003366;
    }
    .section {
      margin-bottom: 30px;
    }
    .section h2 {
      border-bottom: 1px solid #ccc;
      padding-bottom: 10px;
      margin-bottom: 20px;
      color: #003366;
    }
    .info-box {
      margin-bottom: 15px;
    }
    .info-box label {
      font-weight: bold;
    }
    .info-box span {
      margin-left: 10px;
      color: #555;
    }
    /* Formularios */
    form {
      margin-top: 20px;
    }
    form .form-group {
      margin-bottom: 15px;
    }
    form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #333;
    }
    form input[type="text"],
    form input[type="email"],
    form input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }
    form button {
      padding: 10px 20px;
      background-color: #007BFF;
      border: none;
      color: #fff;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 10px;
    }
    form button:hover {
      background-color: #0056b3;
    }
    .alert {
      padding: 12px;
      background-color: #e9f7ef;
      border: 1px solid #c3e6cb;
      color: #155724;
      border-radius: 4px;
      text-align: center;
      margin-bottom: 20px;
    }
    /* Botón para volver al Dashboard */
    .back-btn {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 15px;
      background: #28a745;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s;
    }
    .back-btn:hover {
      background: #1e7e34;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Mi Perfil Administrador</h1>

    <!-- Mostrar mensajes -->
    <?php if (!empty($message)): ?>
      <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (!empty($photoMessage)): ?>
      <div class="alert"><?php echo $photoMessage; ?></div>
    <?php endif; ?>

    <!-- Sección: Datos Personales -->
    <div class="section">
      <h2>Datos Personales</h2>
      <div class="info-box">
        <label>Cédula:</label>
        <span><?php echo htmlspecialchars($adminData['cedula']); ?></span>
      </div>
      <div class="info-box">
        <label>Nombre Completo:</label>
        <span><?php echo htmlspecialchars($adminData['nombre_completo']); ?></span>
      </div>
      <div class="info-box">
        <label>Apellidos:</label>
        <span><?php echo htmlspecialchars($adminData['apellidos']); ?></span>
      </div>
      <div class="info-box">
        <label>Correo:</label>
        <span><?php echo htmlspecialchars($adminData['correo']); ?></span>
      </div>
      <div class="info-box">
        <label>Cargo:</label>
        <span><?php echo htmlspecialchars($adminData['cargo']); ?></span>
      </div>
      <div class="info-box">
        <label>Empresa:</label>
        <span><?php echo htmlspecialchars($adminData['empresa']); ?></span>
      </div>
      <div class="info-box">
        <label>Número de Celular:</label>
        <span><?php echo htmlspecialchars($adminData['celular']); ?></span>
      </div>
    </div>

    <!-- Formulario para actualizar Datos Personales -->
    <div class="section">
      <h2>Actualizar Datos</h2>
      <form action="perfil_admin.php" method="POST">
        <!-- Este campo oculto nos ayuda a identificar que es una actualización de info -->
        <input type="hidden" name="update_info" value="1">
        <div class="form-group">
          <label for="nombre">Nombre Completo:</label>
          <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($adminData['nombre_completo']); ?>" required>
        </div>
        <div class="form-group">
          <label for="apellidos">Apellidos:</label>
          <input type="text" name="apellidos" id="apellidos" value="<?php echo htmlspecialchars($adminData['apellidos']); ?>" required>
        </div>
        <div class="form-group">
          <label for="correo">Correo:</label>
          <input type="email" name="correo" id="correo" value="<?php echo htmlspecialchars($adminData['correo']); ?>" required>
        </div>
        <div class="form-group">
          <label for="cargo">Cargo:</label>
          <input type="text" name="cargo" id="cargo" value="<?php echo htmlspecialchars($adminData['cargo']); ?>" required>
        </div>
        <button type="submit">Actualizar Datos</button>
      </form>
    </div>

    <!-- Formulario para actualizar Foto de Perfil -->
    <div class="section">
      <h2>Actualizar Foto de Perfil</h2>
      <form action="perfil_admin.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="foto">Selecciona una nueva foto:</label>
          <input type="file" name="foto" id="foto" accept="image/*">
        </div>
        <button type="submit">Actualizar Foto</button>
      </form>
    </div>

    <!-- Botón para volver al Dashboard -->
    <a href="dashboard_admin.php" class="back-btn">Volver al Dashboard</a>
  </div>
</body>
</html>
