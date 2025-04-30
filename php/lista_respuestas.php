<?php
// lista_respuestas.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}
require '../php/config.php';

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
if (!$student_id) die("Falta el parámetro student_id");

// Datos del estudiante
$stu = $mysqli->query("
  SELECT cedula, nombre_completo, apellidos
    FROM users
   WHERE id = $student_id
")->fetch_assoc();
if (!$stu) die("Estudiante no encontrado");

// Traer las respuestas
$qr = $mysqli->query("
  SELECT 
    r.id AS resp_id,
    t.title        AS tpl_title,
    r.created_at
  FROM evaluation_responses r
  JOIN evaluation_templates t ON t.id = r.template_id
  WHERE r.professor_id = $student_id
  ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Evaluaciones de <?php echo htmlspecialchars($stu['nombre_completo']) ?></title>
  <link rel="stylesheet" href="../css/dashboard_admin.css">
  <style>
    .btn-back { display:inline-block; margin-bottom:20px; padding:6px 12px; 
                background:#6c757d; color:#fff; text-decoration:none; border-radius:4px; }
    .btn-back:hover { background:#5a6268; }
    table { width:100%; border-collapse:collapse; }
    th,td { padding:8px; border:1px solid #ccc; text-align:left; }
    .btn-pdf { padding:4px 8px; background:#007BFF; color:#fff; text-decoration:none; border-radius:4px; }
    .btn-pdf:hover { background:#0056b3; }
  </style>
</head>
<body>
  <a href="lista_estudiantes.php" class="btn-back">← Volver a Estudiantes</a>
  <h2>Evaluaciones de <?php echo htmlspecialchars($stu['nombre_completo'].' '.$stu['apellidos']) ?></h2>
  <p>Cédula: <?php echo htmlspecialchars($stu['cedula']) ?></p>

  <?php if($qr->num_rows): ?>
    <table>
      <thead>
        <tr><th>Fecha</th><th>Hora</th><th>Plantilla</th><th>Acción</th></tr>
      </thead>
      <tbody>
        <?php while($r = $qr->fetch_assoc()): 
          $dt = new DateTime($r['created_at']);
        ?>
        <tr>
          <td><?php echo $dt->format('Y-m-d') ?></td>
          <td><?php echo $dt->format('H:i:s') ?></td>
          <td><?php echo htmlspecialchars($r['tpl_title']) ?></td>
          <td>
            <a href="report_fpdf.php?response_id=<?php echo $r['resp_id'] ?>"
               class="btn-pdf">PDF</a>
          </td>
        </tr>
        <?php endwhile ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Este estudiante no ha realizado ninguna evaluación.</p>
  <?php endif ?>
</body>
</html>
