<?php
session_start();
if(!isset($_SESSION['user_id'])||$_SESSION['rol']!=='estudiante'){
  header("Location: ../login.html"); exit;
}
require '../php/config.php';

$tpl_id = isset($_GET['tpl'])?(int)$_GET['tpl']:0;
// 1) Cargo plantilla
$stmt = $mysqli->prepare("SELECT * FROM evaluation_templates WHERE id=?");
$stmt->bind_param("i",$tpl_id); $stmt->execute();
$tpl = $stmt->get_result()->fetch_assoc();
if(!$tpl){
  echo "<h2>Plantilla no encontrada</h2>"; exit;
}
// 2) Cargo preguntas
$stmt = $mysqli->prepare("SELECT * FROM evaluation_questions WHERE template_id=?");
$stmt->bind_param("i",$tpl_id); $stmt->execute();
$qs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title><?=htmlspecialchars($tpl['name'])?></title></head>
<body>
  <h1><?=htmlspecialchars($tpl['name'])?></h1>
  <form action="submit_evaluation.php" method="POST">
    <input type="hidden" name="tpl_id" value="<?=$tpl_id?>">
    <?php while($q=$qs->fetch_assoc()): ?>
      <fieldset>
        <legend><?=htmlspecialchars($q['text'])?></legend>
        <?php if($q['type']==='radio'): ?>
          <?php for($i=5;$i>=1;$i--): ?>
            <label>
              <input type="radio" name="q[<?=$q['id']?>]" value="<?=$i?>" required> <?=$i?>
            </label>
          <?php endfor; ?>
        <?php else: ?>
          <textarea name="q[<?=$q['id']?>]" rows="3" required></textarea>
        <?php endif; ?>
      </fieldset>
    <?php endwhile; ?>
    <button type="submit">Enviar Evaluación</button>
  </form>
</body>
</html>
