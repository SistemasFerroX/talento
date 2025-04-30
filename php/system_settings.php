<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$admin_id = $_SESSION['user_id'];
$profileMessage = "";
$updateUserMessage = "";

// Procesar actualización de la foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto']) && !empty($_FILES['foto']['name'])) {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $tmpPath = $_FILES['foto']['tmp_name'];
        $originalName = basename($_FILES['foto']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowedExt)) {
            $newFileName = $admin_id . "_" . time() . "." . $ext;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($tmpPath, $destPath)) {
                $stmt = $mysqli->prepare("UPDATE users SET foto = ? WHERE id = ?");
                $stmt->bind_param("si", $newFileName, $admin_id);
                if ($stmt->execute()) {
                    $profileMessage .= "Foto de perfil actualizada correctamente. ";
                    $_SESSION['foto'] = $newFileName;
                } else {
                    $profileMessage .= "Error al actualizar foto: " . $mysqli->error;
                }
                $stmt->close();
            } else {
                $profileMessage .= "Error al mover el archivo.";
            }
        } else {
            $profileMessage .= "Extensión no permitida. Usa JPG, JPEG, PNG o GIF.";
        }
    } else {
        $profileMessage .= "Error en la subida del archivo.";
    }
}

// Procesar actualización de datos personales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $apellidos = $mysqli->real_escape_string($_POST['apellidos']);
    $correo = $mysqli->real_escape_string($_POST['correo']);
    $cargo = $mysqli->real_escape_string($_POST['cargo']);
    $celular = isset($_POST['celular']) ? $mysqli->real_escape_string($_POST['celular']) : "";
    
    $stmt = $mysqli->prepare("UPDATE users SET cedula = ?, nombre_completo = ?, apellidos = ?, correo = ?, cargo = ?, celular = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $cedula, $nombre, $apellidos, $correo, $cargo, $celular, $admin_id);
    if ($stmt->execute()) {
        $updateUserMessage = "Datos de perfil actualizados correctamente.";
        $_SESSION['nombre'] = $nombre;
    } else {
        $updateUserMessage = "Error al actualizar datos: " . $mysqli->error;
    }
    $stmt->close();
}

// Recuperar la información actualizada del admin
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
  <title>Configuración del Sistema</title>
  <link rel="stylesheet" href="../css/dashboard_admin.css">
  <style>
    /* Estilos para la configuración del admin con más espaciado */
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      margin: 0;
      padding: 30px;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      background: #fff;
      padding: 40px 50px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h1, h2 {
      color: #003366;
      text-align: center;
      margin-bottom: 30px;
    }
    .section {
      margin-bottom: 50px;
    }
    .info-box {
      margin-bottom: 20px;
    }
    .info-box label {
      font-weight: bold;
      display: inline-block;
      min-width: 150px;
      color: #333;
    }
    .info-box span {
      color: #555;
    }
    form {
      margin-top: 30px;
    }
    form .form-group {
      margin-bottom: 25px;
    }
    form label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #333;
    }
    form input[type="text"],
    form input[type="email"],
    form input[type="file"],
    form select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 15px;
    }
    form button {
      padding: 12px 25px;
      background: #007BFF;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 15px;
      font-size: 15px;
    }
    form button:hover {
      background: #0056b3;
    }
    .alert {
      padding: 15px;
      background: #e9f7ef;
      border: 1px solid #c3e6cb;
      color: #155724;
      border-radius: 4px;
      text-align: center;
      margin-bottom: 30px;
    }
    .back-btn {
      display: inline-block;
      margin-top: 30px;
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
    .separator {
      height: 2px;
      background: #ccc;
      margin: 40px 0;
    }
    /* Estilos para botones de enlaces adicionales */
    .report-link {
      display: inline-block;
      margin-top: 20px; /* Ajústalo a 30px o 40px si quieres más separación */
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
  </style>
</head>
<body>
  <div class="container">
    <h1>Configuración del Sistema</h1>

    <!-- Sección: Perfil del Administrador -->
    <div class="section">
      <h2>Mi Perfil</h2>
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
      <?php if (!empty($adminData['foto'])): ?>
        <div class="info-box">
          <label>Foto Actual:</label>
          <br>
          <img src="../uploads/<?php echo htmlspecialchars($adminData['foto']); ?>" alt="Foto de Perfil" style="max-width:150px; border-radius:50%;">
        </div>
      <?php endif; ?>

      <!-- Formulario para actualizar datos personales -->
      <?php if(!empty($updateUserMessage)): ?>
        <div class="alert"><?php echo $updateUserMessage; ?></div>
      <?php endif; ?>
      <form action="perfil_admin.php" method="POST">
        <input type="hidden" name="update_info" value="1">
        <div class="form-group">
          <label for="cedula">Cédula:</label>
          <input type="text" name="cedula" id="cedula" value="<?php echo htmlspecialchars($adminData['cedula']); ?>" required>
        </div>
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
        <div class="form-group">
          <label for="celular">Número de Celular (opcional):</label>
          <input type="text" name="celular" id="celular" value="<?php echo htmlspecialchars($adminData['celular']); ?>">
        </div>
        <button type="submit">Actualizar Datos</button>
      </form>
    </div>

    <div class="separator"></div>
    
    <!-- Sección: Actualizar Foto de Perfil -->
    <div class="section">
      <h2>Actualizar Foto de Perfil</h2>
      <?php if(!empty($profileMessage)): ?>
        <div class="alert"><?php echo $profileMessage; ?></div>
      <?php endif; ?>
      <form action="perfil_admin.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="foto">Selecciona una nueva foto:</label>
          <input type="file" name="foto" id="foto" accept="image/*">
        </div>
        <button type="submit">Actualizar Foto</button>
      </form>
    </div>

    <div class="separator"></div>
    
    <!-- Sección: Gestión de Usuarios -->
    <div class="section">
      <h2>Gestión de Usuarios</h2>
      <p>Desde aquí puedes ver y editar los datos de cualquier usuario (estudiantes, profesores, etc.).</p>
      <a href="lista_usuarios.php" class="report-link">Ver Lista de Usuarios</a>
    </div>
    
    <div class="separator"></div>
    


    <!-- Botón para volver al Dashboard -->
    <div style="text-align:center; margin-top:40px;">
      <a href="dashboard_admin.php" class="back-btn">Volver al Dashboard</a>
    </div>
  </div>
</body>
</html>
