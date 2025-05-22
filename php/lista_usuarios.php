<?php
session_start();
require('config.php');

// 1) Verificar autenticación y rol de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}

// 2) Procesar eliminación de usuario (si se solicitó)
if (isset($_GET['delete_user'])) {
    $toDelete = (int) $_GET['delete_user'];
    // Evitar que un admin se borre a sí mismo
    if ($toDelete !== (int)$_SESSION['user_id']) {
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $toDelete);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Usuario eliminado correctamente.";
    } else {
        $mensaje = "No puedes eliminar tu propia cuenta.";
    }
}

// 3) Recuperar todos los usuarios
$query  = "SELECT id, cedula, nombre_completo, apellidos, correo, rol, empresa 
           FROM users 
           ORDER BY nombre_completo ASC";
$result = $mysqli->query($query);
if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #003366;
      margin-bottom: 20px;
    }
    .mensaje {
      width: 90%;
      max-width: 800px;
      margin: 10px auto;
      padding: 10px;
      background: #e9f7ef;
      border: 1px solid #c3e6cb;
      color: #155724;
      border-radius: 4px;
      text-align: center;
    }
    table {
      width: 90%;
      max-width: 800px;
      margin: 0 auto 20px;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 10px 12px;
      border: 1px solid #ccc;
      text-align: center;
      font-size: 0.95em;
    }
    th {
      background-color: #003366;
      color: #fff;
    }
    .actions {
      display: flex;
      justify-content: center;
      gap: 8px;
    }
    .action-btn {
      display: inline-block;
      padding: 6px 12px;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.9em;
      transition: background 0.3s;
    }
    .btn-edit {
      background-color: #007BFF;
    }
    .btn-edit:hover {
      background-color: #0056b3;
    }
    .btn-delete {
      background-color: #dc3545;
    }
    .btn-delete:hover {
      background-color: #c82333;
    }
    .back-button {
      display: inline-block;
      margin: 0 auto;
      padding: 8px 14px;
      background: #28a745;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.9em;
      box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .footer {
      text-align: center;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <h1>Gestión de Usuarios</h1>

  <!-- Mensaje de operación -->
  <?php if (!empty($mensaje)): ?>
    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Cédula</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Correo</th>
        <th>Rol</th>
        <th>Empresa</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($user['id']) ?></td>
          <td><?= htmlspecialchars($user['cedula']) ?></td>
          <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
          <td><?= htmlspecialchars($user['apellidos']) ?></td>
          <td><?= htmlspecialchars($user['correo']) ?></td>
          <td><?= htmlspecialchars($user['rol']) ?></td>
          <td><?= htmlspecialchars($user['empresa']) ?></td>
          <td>
            <div class="actions">
              <a 
                href="editar_usuario.php?user_id=<?= $user['id'] ?>" 
                class="action-btn btn-edit"
              >Editar</a>
              <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                <a 
                  href="lista_usuarios.php?delete_user=<?= $user['id'] ?>" 
                  class="action-btn btn-delete"
                  onclick="return confirm('¿Eliminar a <?= addslashes(htmlspecialchars($user['nombre_completo'])) ?>?');"
                >Eliminar</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div class="footer">
    <a href="dashboard_admin.php" class="back-button">← Volver al Dashboard</a>
  </div>

</body>
</html>
