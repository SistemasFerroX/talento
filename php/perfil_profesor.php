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

// Verificar si el usuario está autenticado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}

require 'config.php';

$profesor_id = $_SESSION['user_id'];
$mensaje = "";

// Obtener la información del profesor (incluyendo el número de celular)
$query = "SELECT * FROM users WHERE id = $profesor_id";
$result = $mysqli->query($query);
$profesor = $result->fetch_assoc();

// Procesar la actualización de la foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName    = $_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg','jpeg','png','gif');
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = $profesor_id . "_" . time() . "." . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $updateQuery = "UPDATE users SET foto = '$newFileName' WHERE id = $profesor_id";
                if ($mysqli->query($updateQuery)) {
                    $mensaje = "Foto de perfil actualizada exitosamente.";
                    $query = "SELECT * FROM users WHERE id = $profesor_id";
                    $result = $mysqli->query($query);
                    $profesor = $result->fetch_assoc();
                } else {
                    $mensaje = "Error al actualizar la foto: " . $mysqli->error;
                }
            } else {
                $mensaje = "Error al mover el archivo a la carpeta de subida.";
            }
        } else {
            $mensaje = "Extensión no permitida. Usa JPG, JPEG, PNG o GIF.";
        }
    } else {
        $mensaje = "No se seleccionó ningún archivo o ocurrió un error en la subida.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil Profesor</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      padding: 20px;
    }
    .back-dashboard {
      display: inline-block;
      margin-bottom: 20px;
      padding: 10px 20px;
      background-color: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      text-align: center;
    }
    .back-dashboard:hover {
      background-color: #0056b3;
    }
    .profile-container {
      background-color: #fff;
      max-width: 600px;
      margin: 40px auto;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .profile-header { text-align: center; margin-bottom: 20px; }
    .profile-header h1 { font-size: 28px; color: #003366; }
    .profile-photo { text-align: center; margin-bottom: 20px; }
    .profile-photo img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #007BFF;
    }
    .profile-info { text-align: center; margin-bottom: 20px; }
    .profile-info p { font-size: 18px; margin: 10px 0; color: #333; }
    .update-form {
      margin-top: 30px;
      border-top: 1px solid #ccc;
      padding-top: 20px;
    }
    .update-form h2 { font-size: 22px; color: #003366; text-align: center; margin-bottom: 15px; }
    .update-form label {
      display: block;
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 5px;
      color: #333;
    }
    .update-form input[type="file"] {
      width: 100%;
      padding: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .update-form button {
      display: block;
      width: 100%;
      padding: 12px;
      font-size: 18px;
      color: #fff;
      background-color: #007BFF;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .update-form button:hover { background-color: #0056b3; }
    .mensaje {
      background: #e9f7ef;
      border: 1px solid #c3e6cb;
      padding: 12px;
      color: #155724;
      text-align: center;
      margin-bottom: 20px;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <a href="dashboard_profesor.php" class="back-dashboard">Volver al Dashboard</a>
  
  <div class="profile-container">
    <div class="profile-header">
      <h1>Perfil del Profesor</h1>
    </div>
    
    <?php if(isset($mensaje) && !empty($mensaje)): ?>
      <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <div class="profile-photo">
      <?php if(!empty($profesor['foto'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($profesor['foto']); ?>" alt="Foto de Perfil">
      <?php else: ?>
        <img src="../images/default-profile.png" alt="Foto de Perfil">
      <?php endif; ?>
    </div>
    
    <div class="profile-info">
      <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($profesor['nombre_completo'] . " " . $profesor['apellidos']); ?></p>
      <p><strong>Correo:</strong> <?php echo htmlspecialchars($profesor['correo']); ?></p>
      <p><strong>Cédula:</strong> <?php echo htmlspecialchars($profesor['cedula']); ?></p>
      <p><strong>Rol:</strong> <?php echo htmlspecialchars($profesor['rol']); ?></p>
      <p><strong>Empresa:</strong> <?php echo htmlspecialchars($profesor['empresa']); ?></p>
      <p><strong>Sub-Empresa:</strong> <?php echo htmlspecialchars($profesor['sub_empresa']); ?></p>
      <p><strong>Sub-Sub-Empresa:</strong> <?php echo htmlspecialchars($profesor['sub_sub_empresa']); ?></p>
      <p><strong>Cargo:</strong> <?php echo htmlspecialchars($profesor['cargo']); ?></p>
      <p><strong>Celular:</strong> <?php echo htmlspecialchars($profesor['celular']); ?></p>
      <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($profesor['fecha_creacion']); ?></p>
    </div>
    
    <div class="update-form">
      <h2>Actualizar Foto de Perfil</h2>
      <form action="perfil_profesor.php" method="POST" enctype="multipart/form-data">
        <label for="foto">Selecciona una nueva foto:</label>
        <input type="file" name="foto" id="foto" accept="image/*" required>
        <button type="submit">Actualizar Foto</button>
      </form>
    </div>
  </div>
</body>
</html>
