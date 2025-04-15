<?php
session_start();
require('config.php');

// Verificar que el usuario esté autenticado y tenga rol admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

if (!isset($_GET['user_id'])) {
    die("No se especificó el ID del usuario.");
}

$user_id = (int) $_GET['user_id'];
$message = "";

// Procesar la actualización del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $apellidos = $mysqli->real_escape_string($_POST['apellidos']);
    $correo = $mysqli->real_escape_string($_POST['correo']);
    $rol = $mysqli->real_escape_string($_POST['rol']);
    $empresa = $mysqli->real_escape_string($_POST['empresa']);
    // Los campos opcionales
    $sub_empresa = isset($_POST['sub_empresa']) ? $mysqli->real_escape_string($_POST['sub_empresa']) : "";
    $sub_sub_empresa = isset($_POST['sub_sub_empresa']) ? $mysqli->real_escape_string($_POST['sub_sub_empresa']) : "";
    $cargo = $mysqli->real_escape_string($_POST['cargo']);
    $celular = isset($_POST['celular']) ? $mysqli->real_escape_string($_POST['celular']) : "";
    
    $stmt = $mysqli->prepare("UPDATE users SET cedula = ?, nombre_completo = ?, apellidos = ?, correo = ?, rol = ?, empresa = ?, sub_empresa = ?, sub_sub_empresa = ?, cargo = ?, celular = ? WHERE id = ?");
    $stmt->bind_param("ssssssssssi", $cedula, $nombre, $apellidos, $correo, $rol, $empresa, $sub_empresa, $sub_sub_empresa, $cargo, $celular, $user_id);
    if ($stmt->execute()) {
        $message = "Usuario actualizado correctamente.";
    } else {
        $message = "Error al actualizar: " . $mysqli->error;
    }
    $stmt->close();
}

// Obtener los datos actuales del usuario
$query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = $mysqli->query($query);
if (!$result || $result->num_rows == 0) {
    die("Usuario no encontrado.");
}
$userData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #fff;
      padding: 20px 30px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #003366;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
      color: #333;
    }
    input[type="text"],
    input[type="email"],
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }
    button {
      padding: 10px 20px;
      background-color: #007BFF;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      margin-top: 10px;
    }
    button:hover {
      background-color: #0056b3;
    }
    .alert {
      padding: 10px;
      background-color: #e9f7ef;
      border: 1px solid #c3e6cb;
      color: #155724;
      border-radius: 4px;
      text-align: center;
      margin-bottom: 20px;
    }
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
    <h1>Editar Usuario</h1>

    <?php if (!empty($message)): ?>
      <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="editar_usuario.php?user_id=<?php echo $user_id; ?>" method="POST">
      <div class="form-group">
        <label for="cedula">Cédula:</label>
        <input type="text" name="cedula" id="cedula" value="<?php echo htmlspecialchars($userData['cedula']); ?>" required>
      </div>
      <div class="form-group">
        <label for="nombre">Nombre Completo:</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($userData['nombre_completo']); ?>" required>
      </div>
      <div class="form-group">
        <label for="apellidos">Apellidos:</label>
        <input type="text" name="apellidos" id="apellidos" value="<?php echo htmlspecialchars($userData['apellidos']); ?>" required>
      </div>
      <div class="form-group">
        <label for="correo">Correo:</label>
        <input type="email" name="correo" id="correo" value="<?php echo htmlspecialchars($userData['correo']); ?>" required>
      </div>
      <div class="form-group">
        <label for="rol">Rol:</label>
        <select name="rol" id="rol" required>
          <option value="estudiante" <?php if ($userData['rol'] == 'estudiante') echo 'selected'; ?>>Estudiante</option>
          <option value="profesor" <?php if ($userData['rol'] == 'profesor') echo 'selected'; ?>>Profesor</option>
          <option value="admin" <?php if ($userData['rol'] == 'admin') echo 'selected'; ?>>Administrador</option>
        </select>
      </div>
      <div class="form-group">
        <label for="empresa">Empresa:</label>
        <select name="empresa" id="empresa" required>
          <option value="Inversiones Ferbienes" <?php if($userData['empresa'] == 'Inversiones Ferbienes') echo 'selected'; ?>>Inversiones Ferbienes</option>
          <option value="Comercializadora Agrosigo" <?php if($userData['empresa'] == 'Comercializadora Agrosigo') echo 'selected'; ?>>Comercializadora Agrosigo</option>
          <option value="Inversiones Tribilin" <?php if($userData['empresa'] == 'Inversiones Tribilin') echo 'selected'; ?>>Inversiones Tribilin</option>
          <option value="Agrosigo" <?php if($userData['empresa'] == 'Agrosigo') echo 'selected'; ?>>Agrosigo</option>
        </select>
      </div>
      <!-- Opcionales: Sub Empresa y Sub Sub Empresa -->
      <div class="form-group">
        <label for="sub_empresa">Sub Empresa:</label>
        <input type="text" name="sub_empresa" id="sub_empresa" value="<?php echo htmlspecialchars($userData['sub_empresa']); ?>">
      </div>
      <div class="form-group">
        <label for="sub_sub_empresa">Sub Sub Empresa:</label>
        <input type="text" name="sub_sub_empresa" id="sub_sub_empresa" value="<?php echo htmlspecialchars($userData['sub_sub_empresa']); ?>">
      </div>
      <div class="form-group">
        <label for="cargo">Cargo:</label>
        <input type="text" name="cargo" id="cargo" value="<?php echo htmlspecialchars($userData['cargo']); ?>" required>
      </div>
      <div class="form-group">
        <label for="celular">Número de Celular (opcional):</label>
        <input type="text" name="celular" id="celular" value="<?php echo htmlspecialchars($userData['celular']); ?>">
      </div>
      <button type="submit">Actualizar Usuario</button>
    </form>
    <a href="lista_usuarios.php" class="back-btn">Volver a la Lista de Usuarios</a>
  </div>
</body>
</html>
