<?php
session_start();
require('config.php');

// Verificar que el usuario esté autenticado y tenga rol admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

// Consulta para obtener todos los usuarios ordenados por nombre
$query = "SELECT id, cedula, nombre_completo, apellidos, correo, rol, empresa FROM users ORDER BY nombre_completo ASC";
$result = $mysqli->query($query);
if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Usuarios - Gestión de Usuarios</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #003366;
    }
    table {
      width: 90%;
      margin: 20px auto;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th {
      background-color: #003366;
      color: #fff;
    }
    .action-btn {
      padding: 5px 10px;
      background-color: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s;
    }
    .action-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <h1>Gestión de Usuarios</h1>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Cédula</th>
        <th>Nombre Completo</th>
        <th>Apellidos</th>
        <th>Correo</th>
        <th>Rol</th>
        <th>Empresa</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($user['id']); ?></td>
          <td><?php echo htmlspecialchars($user['cedula']); ?></td>
          <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
          <td><?php echo htmlspecialchars($user['apellidos']); ?></td>
          <td><?php echo htmlspecialchars($user['correo']); ?></td>
          <td><?php echo htmlspecialchars($user['rol']); ?></td>
          <td><?php echo htmlspecialchars($user['empresa']); ?></td>
          <td>
            <a class="action-btn" href="editar_usuario.php?user_id=<?php echo $user['id']; ?>">Editar</a>
            <!-- Aquí podrías agregar otro botón para eliminar, si lo deseas -->
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <!-- Botón para volver al Dashboard -->
  <div style="text-align:center; margin-top:20px;">
    <a href="dashboard_admin.php" class="action-btn" style="background-color:#28a745;">Volver al Dashboard</a>
  </div>
</body>
</html>
