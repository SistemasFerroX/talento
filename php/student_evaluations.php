<?php
session_start();
require __DIR__ . '/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
if (!$student_id) die("Falta student_id");

// Nombre del estudiante
$stmt = $mysqli->prepare("SELECT nombre_completo FROM users WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$nm = $stmt->get_result()->fetch_assoc()['nombre_completo'];

// Traer evaluaciones
$sql = "SELECT r.id, t.title, r.created_at
          FROM evaluation_responses r
          JOIN evaluation_templates t ON t.id = r.template_id
         WHERE r.professor_id = ?
      ORDER BY r.created_at DESC";
$stmt2 = $mysqli->prepare($sql);
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$resps = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Evaluaciones de <?= htmlspecialchars($nm) ?></title>
  <link rel="stylesheet" href="../css/dashboard_admin.css">
  <style>
    /* Banner */
    .banner {
      width: 100%;
      overflow: hidden;
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
    /* Contenedor principal */
    .container {
      max-width: 960px;
      margin: 0 auto 40px;
      padding: 0 15px;
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
    /* PDF button */
    .btn-pdf {
      background: #28a745;
      color: #fff;
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-size: .9em;
      transition: background .3s;
    }
    .btn-pdf:hover {
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
    <h1>Evaluaciones de <?= htmlspecialchars($nm) ?></h1>
  </header>

  <!-- Botones de navegación -->
  <div class="nav-buttons">
    <a href="lista_estudiantes_eval.php" class="btn-secondary">← Volver al listado</a>
    <a href="dashboard_admin.php"     class="btn-primary">Dashboard Admin</a>
  </div>

  <!-- Tabla de respuestas -->
  <div class="container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Plantilla</th>
          <th>Fecha / Hora</th>
          <th>PDF</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($r = $resps->fetch_assoc()): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= $r['created_at'] ?></td>
            <td>
              <a class="btn-pdf"
                 href="report_eval.php?response_id=<?= $r['id'] ?>"
                 target="_blank">
                PDF
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
