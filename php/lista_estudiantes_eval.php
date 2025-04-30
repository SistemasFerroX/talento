<?php
session_start();
require __DIR__ . '/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}
// Traigo estudiantes
$sql = "SELECT id, nombre_completo, cedula 
          FROM users 
         WHERE rol='estudiante' 
      ORDER BY nombre_completo";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Evaluaciones por Estudiante</title>
  <link rel="stylesheet" href="../css/dashboard_admin.css">
  <style>
    /* Banner */
    .banner {
      width: 100%;
      overflow: hidden;
      margin-bottom: 0;
    }
    .banner img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      display: block;
    }
    /* Cabecera */
    .header {
      background: #003366;
      color: #fff;
      text-align: center;
      padding: 20px 0;
    }
    .header h1 {
      margin: 0;
      font-size: 1.8em;
    }
    /* Botones de navegación */
    .nav-buttons {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin: 15px 0;
    }
    .nav-buttons a {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
      transition: background .3s;
    }
    .btn-primary {
      background: #007bff;
      color: #fff;
    }
    .btn-primary:hover {
      background: #0056b3;
    }
    .btn-secondary {
      background: #6c757d;
      color: #fff;
    }
    .btn-secondary:hover {
      background: #5a6268;
    }
    /* Contenedor */
    .container {
      max-width: 960px;
      margin: 0 auto 40px;
      padding: 0 15px;
    }
    /* Buscador */
    #search {
      width: 100%;
      max-width: 300px;
      padding: 8px;
      margin: 0 auto;
      display: block;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    /* Tabla */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #003366;
      color: #fff;
      font-size: 1em;
    }
    tbody tr:nth-child(odd) {
      background: #f9f9f9;
    }
    /* Botón Ver */
    .btn-view {
      background: #28a745;
      color: #fff;
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-size: .9em;
      transition: background .3s;
    }
    .btn-view:hover {
      background: #218838;
    }
  </style>
</head>
<body>

  <!-- Banner -->
  <div class="banner">
    <img src="../images/talento2.png" alt="Banner Talento+">
  </div>

  <!-- Cabecera -->
  <header class="header">
    <h1>Evaluaciones por Estudiante</h1>
  </header>

  <!-- Botones de navegación -->
  <div class="nav-buttons">
    <a href="dashboard_admin.php" class="btn-primary">← Volver al Dashboard</a>
  </div>

  <div class="container">
    <!-- Buscador -->
    <input id="search" placeholder="Buscar por cédula…">

    <!-- Tabla -->
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Cédula</th>
          <th>Nombre</th>
          <th>Ver Evaluaciones</th>
        </tr>
      </thead>
      <tbody id="tabla">
        <?php while ($u = $res->fetch_assoc()): ?>
          <tr data-ced="<?= htmlspecialchars(strtolower($u['cedula'])) ?>">
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['cedula']) ?></td>
            <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
            <td>
              <a class="btn-view" href="student_evaluations.php?student_id=<?= $u['id'] ?>">
                Ver
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Filtrado JS -->
  <script>
    document.getElementById('search').addEventListener('input', function(){
      const q = this.value.trim().toLowerCase();
      document.querySelectorAll('#tabla tr').forEach(tr => {
        tr.style.display = !q || tr.dataset.ced.includes(q) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
