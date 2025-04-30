<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    // Solo estudiantes pueden evaluar
    header("Location: ../login.html");
    exit;
}

require '../php/config.php';

// 1) Recuperar slug de la plantilla
$slug = $_GET['template'] ?? '';
if (!$slug) {
    die("Falta el parámetro de plantilla.");
}

// 2) Buscar la plantilla
$stmtTpl = $mysqli->prepare("
  SELECT id, title 
    FROM evaluation_templates 
   WHERE slug = ?
");
$stmtTpl->bind_param("s", $slug);
$stmtTpl->execute();
$tpl = $stmtTpl->get_result()->fetch_assoc();
if (!$tpl) {
    die("Plantilla no encontrada.");
}
$tpl_id    = $tpl['id'];
$tpl_title = $tpl['title'];

// 3) Si vino POST, procesar el envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3.1) Insertar el envío
    $stmtIns = $mysqli->prepare("
      INSERT INTO evaluation_responses (professor_id, template_id) 
      VALUES (?, ?)
    ");
    // Guardamos el ID de quien evalúa (estudiante) en professor_id
    $stmtIns->bind_param("ii", $_SESSION['user_id'], $tpl_id);
    $stmtIns->execute();
    $response_id = $stmtIns->insert_id;

    // 3.2) Insertar cada respuesta
    $total = 0;
    $count = 0;
    foreach ($_POST['answer'] as $q_id => $val) {
        $v = (int)$val;
        $stmtA = $mysqli->prepare("
          INSERT INTO evaluation_answers (response_id, question_id, answer_value)
          VALUES (?, ?, ?)
        ");
        $stmtA->bind_param("iis", $response_id, $q_id, $v);
        $stmtA->execute();
        $total += $v;
        $count++;
    }
    // 3.3) Promedio
    $avg = $count ? round($total / $count, 2) : 0;

    // 3.4) Mostrar agradecimiento + enlace PDF
    echo <<<HTML
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Gracias</title>
<style>
  body{font-family:sans-serif;text-align:center;padding:40px;}
  .btn{display:inline-block;margin-top:20px;padding:10px 20px;
       background:#0066cc;color:#fff;text-decoration:none;border-radius:4px;}
  .btn:hover{background:#005bb5;}
</style>
</head><body>
  <h1>¡Gracias por tu evaluación!</h1>
  <p>Tu calificación promedio es: <strong>{$avg}</strong></p>
  <a class="btn" href="report_fpdf.php?response_id={$response_id}" target="_blank">
    Descargar informe en PDF
  </a>
</body></html>
HTML;
    exit;
}

// 4) Traer las preguntas de esta plantilla
$stmtQ = $mysqli->prepare("
  SELECT id, text 
    FROM evaluation_questions 
   WHERE template_id = ? 
ORDER BY qorder ASC
");
$stmtQ->bind_param("i", $tpl_id);
$stmtQ->execute();
$questions = $stmtQ->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($tpl_title) ?></title>
  <style>
    body { font-family:'Segoe UI',sans-serif; background:#f8f9fa; margin:0; padding:0; }
    .container { max-width:800px; margin:40px auto; background:#fff;
                 padding:30px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
    h1 { text-align:center; color:#003366; margin-bottom:30px; }
    .question-card { border:1px solid #eee; border-radius:6px;
                     padding:20px; margin-bottom:20px; }
    .question-text { font-weight:bold; color:#333; margin-bottom:12px; }
    .options label { display:flex; align-items:center;
                     margin-bottom:10px; cursor:pointer; color:#444; }
    .options input { margin-right:10px; transform:scale(1.1); }
    .btn-submit { display:inline-block; background:#0066cc; color:#fff;
                  padding:10px 25px; border:none; border-radius:4px;
                  font-size:1rem; cursor:pointer; transition:background .2s;}
    .btn-submit:hover { background:#005bb5; }
  </style>
</head>
<body>
  <div class="container">
    <h1><?= htmlspecialchars($tpl_title) ?></h1>
    <form method="POST">
      <?php while($q = $questions->fetch_assoc()): ?>
      <div class="question-card">
        <p class="question-text"><?= htmlspecialchars($q['text']) ?></p>
        <div class="options">
          <?php for($v=5;$v>=1;$v--): ?>
          <label>
            <input type="radio"
                   name="answer[<?= $q['id'] ?>]"
                   value="<?= $v ?>"
                   required>
            <span><?= $v ?></span>
          </label>
          <?php endfor; ?>
        </div>
      </div>
      <?php endwhile; ?>

      <div style="text-align:center;">
        <button type="submit" class="btn-submit">Enviar evaluación</button>
      </div>
    </form>
  </div>
</body>
</html>
