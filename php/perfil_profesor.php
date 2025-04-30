<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}

require 'config.php';

$profesor_id = $_SESSION['user_id'];
$mensaje = "";

$query  = "SELECT * FROM users WHERE id = $profesor_id";
$result = $mysqli->query($query);
$profesor = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $tmp  = $_FILES['foto']['tmp_name'];
        $name = $_FILES['foto']['name'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (in_array($ext, $allowed)) {
            $newName = $profesor_id . "_" . time() . "." . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($tmp, $dest)) {
                $mysqli->query("UPDATE users SET foto = '$newName' WHERE id = $profesor_id");
                $mensaje = "Foto de perfil actualizada exitosamente.";
                $result = $mysqli->query($query);
                $profesor = $result->fetch_assoc();
            } else {
                $mensaje = "Error al mover el archivo.";
            }
        } else {
            $mensaje = "Extensión no permitida. Usa JPG, PNG o GIF.";
        }
    } else {
        $mensaje = "No se seleccionó ningún archivo o hubo un error.";
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
    }
    .back-dashboard:hover {
      background-color: #0056b3;
    }

    /* ----- CONTENEDOR ALINEADO IZQUIERDA ----- */
    .profile-container {
      background-color: #fff;
      max-width: 600px;
      margin: 40px 0 40px 20px;  /* 20px desde el borde izquierdo */
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: left;          /* Todo el texto a la izquierda */
    }
    .profile-header h1 {
      font-size: 28px;
      color: #003366;
      margin-bottom: 20px;
      text-align: left;
    }
    .profile-photo {
      float: left;
      margin-right: 20px;
    }
    .profile-photo img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #007BFF;
    }
    .profile-info {
      overflow: hidden;  /* Para envolver junto al float */
    }
    .profile-info p {
      font-size: 18px;
      margin: 8px 0;
      color: #333;
      text-align: left;
    }

    .update-form {
      clear: both;
      margin-top: 30px;
      border-top: 1px solid #ccc;
      padding-top: 20px;
      text-align: left;
    }
    .update-form h2 {
      font-size: 22px;
      color: #003366;
      margin-bottom: 15px;
    }
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
    }
    .update-form button:hover {
      background-color: #0056b3;
    }

    .mensaje {
      background: #e9f7ef;
      border: 1px solid #c3e6cb;
      padding: 12px;
      color: #155724;
      margin-bottom: 20px;
      border-radius: 4px;
      text-align: left;
    }
  </style>
</head>
<body>

  <a href="dashboard_profesor.php" class="back-dashboard">Volver al Dashboard</a>

  <div class="profile-container">
    <div class="profile-header">
      <h1>Perfil del Profesor</h1>
    </div>

    <?php if($mensaje): ?>
      <div class="mensaje"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="profile-photo">
      <?php if(!empty($profesor['foto'])): ?>
        <img src="../uploads/<?= htmlspecialchars($profesor['foto']) ?>" alt="Foto de Perfil">
      <?php else: ?>
        <img src="../images/default-profile.png" alt="Foto de Perfil">
      <?php endif; ?>
    </div>

    <div class="profile-info">
      <p><strong>Nombre Completo:</strong> <?= htmlspecialchars($profesor['nombre_completo'] . ' ' . $profesor['apellidos']) ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($profesor['correo']) ?></p>
      <p><strong>Cédula:</strong> <?= htmlspecialchars($profesor['cedula']) ?></p>
      <p><strong>Rol:</strong> <?= htmlspecialchars($profesor['rol']) ?></p>
      <p><strong>Empresa:</strong> <?= htmlspecialchars($profesor['empresa']) ?></p>
      <p><strong>Sub-Empresa:</strong> <?= htmlspecialchars($profesor['sub_empresa']) ?></p>
      <p><strong>Sub-Sub-Empresa:</strong> <?= htmlspecialchars($profesor['sub_sub_empresa']) ?></p>
      <p><strong>Cargo:</strong> <?= htmlspecialchars($profesor['cargo']) ?></p>
      <p><strong>Celular:</strong> <?= htmlspecialchars($profesor['celular']) ?></p>
      <p><strong>Fecha de Creación:</strong> <?= htmlspecialchars($profesor['fecha_creacion']) ?></p>
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
