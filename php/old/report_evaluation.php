<?php
session_start();
if(!isset($_SESSION['rol'])||$_SESSION['rol']!=='admin'){
  header("Location: ../login.html"); exit;
}
require '../php/config.php';

// 1) Lista de plantillas
$stmt = $mysqli->prepare("SELECT id,name FROM evaluation_templates");
$stmt->execute();
$tpls = $stmt->get_result();

if($_SERVER['REQUEST_METHOD']==='POST'){
  // 2) Si llegó POST con tpl_id → genero PDF
  $tpl_id = (int)$_POST['tpl_id'];
  header("Location: report_evaluation_fpdf.php?tpl=$tpl_id");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Informe Evaluaciones</title></head>
<body>
  <h1>Informe de Evaluaciones</h1>
  <form method="POST">
    <label>Selecciona Evaluación:</label>
    <select name="tpl_id" required>
      <option value="">--</option>
      <?php while($t=$tpls->fetch_assoc()): ?>
        <option value="<?=$t['id']?>"><?=htmlspecialchars($t['name'])?></option>
      <?php endwhile; ?>
    </select>
    <button>Generar PDF</button>
  </form>
</body>
</html>
