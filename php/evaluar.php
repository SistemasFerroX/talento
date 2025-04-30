<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../login.html");
    exit;
}
require '../php/config.php'; // Ajusta ruta si fuera necesario

// 1) Recoger template_id
$template_id = isset($_GET['template_id']) ? (int)$_GET['template_id'] : 0;
if (!$template_id) {
    die("Falta el parámetro template_id");
}

// 2) Procesar envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];

    // Insertamos el envío (incluyendo datos de a quién se evalúa)
    $insert = $mysqli->prepare("
      INSERT INTO evaluation_responses
        (professor_id, template_id, evaluado_nombre, evaluado_cargo)
      VALUES (?, ?, ?, ?)
    ");
    $eval_nombre = $mysqli->real_escape_string($_POST['evaluado_nombre']  ?? '');
    $eval_cargo  = $mysqli->real_escape_string($_POST['evaluado_cargo']   ?? '');
    $insert->bind_param("iiss", $student_id, $template_id, $eval_nombre, $eval_cargo);
    $insert->execute();
    $response_id = $insert->insert_id;

    // Para el promedio
    $total = 0;
    $count = 0;

    // Recuperamos preguntas
    $questions = $mysqli->query("
      SELECT id, question_type
        FROM evaluation_questions
       WHERE template_id = $template_id
    ");

    while ($q = $questions->fetch_assoc()) {
        $qid   = $q['id'];
        $field = "q_{$qid}";
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            continue;
        }

        if ($q['question_type'] === 'radio') {
            // inserto valor numérico
            $val = (int) $_POST[$field];
            $ins = $mysqli->prepare("
              INSERT INTO evaluation_answers
                (response_id, question_id, answer_value, answer_text)
              VALUES (?, ?, ?, '')
            ");
            $ins->bind_param("iii", $response_id, $qid, $val);
            $total += $val;
            $count++;
        } else {
            // inserto texto libre
            $txt = $mysqli->real_escape_string($_POST[$field]);
            $ins = $mysqli->prepare("
              INSERT INTO evaluation_answers
                (response_id, question_id, answer_value, answer_text)
              VALUES (?, ?, 0, ?)
            ");
            $ins->bind_param("iis", $response_id, $qid, $txt);
        }

        $ins->execute();
    }

    // 3) Calculamos promedio y redirigimos
    $avg = $count > 0 ? number_format($total / $count, 2) : '0.00';
    header("Location: thanks.php?avg=" . urlencode($avg));
    exit;
}

// 4) Traer plantilla
$tpl = $mysqli->query("
  SELECT title, banner
    FROM evaluation_templates
   WHERE id = $template_id
")->fetch_assoc();
if (!$tpl) die("Plantilla no encontrada");

// 5) Traer preguntas
$questions = $mysqli->query("
  SELECT id, text, question_type
    FROM evaluation_questions
   WHERE template_id = $template_id
   ORDER BY sort_order ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($tpl['title']) ?></title>
  <link rel="stylesheet" href="../css/dashboard_estudiante.css">
  <style>
    /* Barra superior */
    .top-bar{display:flex;justify-content:space-between;padding:10px 20px;background:#003366;color:#fff}
    .top-bar .logo{height:40px;margin-right:10px}
    .top-bar a{color:#fff;text-decoration:none;margin-left:15px}

    /* Banner */
    .banner img{width:100%;display:block;margin-bottom:20px}

    /* Contenedor */
    .container{max-width:720px;margin:0 auto;padding:0 15px}
    h1{color:#003366;text-align:center;margin:20px 0}

    /* Tarjeta de pregunta */
    .question-card{background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:16px;margin-bottom:16px}
    .question-card p{font-weight:bold;margin-bottom:10px}
    .options label{margin-right:12px;font-weight:normal}
    textarea, input[type="text"]{width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;resize:vertical}

    /* Botones */
    .form-actions{display:flex;justify-content:space-between;align-items:center;margin:24px 0}
    .btn{padding:10px 20px;border:none;border-radius:4px;cursor:pointer;font-size:1em}
    .btn-back{background:#6c757d;color:#fff;text-decoration:none;text-align:center;line-height:1.5}
    .btn-submit{background:#007BFF;color:#fff}
    .btn-back:hover{background:#5a6268}
    .btn-submit:hover{background:#0056b3}
  </style>
</head>
<body>

  <!-- Barra superior -->
  <header class="top-bar">
    <div>
      <img src="../images/logo.png" alt="Logo" class="logo">
      <span>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
    </div>
    <div>
      <a href="dashboard_estudiante.php">Inicio</a>
      <a href="logout.php">Cerrar Sesión</a>
    </div>
  </header>

  <!-- Banner plantilla -->
  <div class="banner">
    <?php if ($tpl['banner']): ?>
      <img src="../uploads/<?= htmlspecialchars($tpl['banner']) ?>" alt="">
    <?php else: ?>
      <img src="../images/talento1.png" alt="">
    <?php endif ?>
  </div>

  <div class="container">
    <h1><?= htmlspecialchars($tpl['title']) ?></h1>

    <form method="post">
      <?php if ($template_id !== 1): ?>
        <div class="question-card">
          <p>Nombre de la persona que vas a evaluar <span style="color:red">*</span></p>
          <input type="text" name="evaluado_nombre" required>
        </div>
        <div class="question-card">
          <p>Cargo de la persona que vas a evaluar <span style="color:red">*</span></p>
          <input type="text" name="evaluado_cargo" required>
        </div>
      <?php endif ?>

      <?php while ($q = $questions->fetch_assoc()): ?>
        <div class="question-card">
          <p><?= htmlspecialchars($q['text']) ?> <span style="color:red">*</span></p>
          <?php if ($q['question_type'] === 'radio'): ?>
            <div class="options">
              <?php for ($i=5; $i>=1; $i--): ?>
                <label>
                  <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $i ?>" required> <?= $i ?>
                </label>
              <?php endfor ?>
            </div>
          <?php else: ?>
            <textarea name="q_<?= $q['id'] ?>" rows="3" required></textarea>
          <?php endif ?>
        </div>
      <?php endwhile ?>

      <div class="form-actions">
        <a href="evaluaciones.php" class="btn btn-back">← Volver</a>
        <button type="submit" class="btn btn-submit">Enviar Evaluación</button>
      </div>
    </form>
  </div>

</body>
</html>
