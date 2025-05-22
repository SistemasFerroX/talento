<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['rol']; // estudiante | profesor | admin
$mensaje   = "";

// Enlaces según rol
if ($user_role === 'admin') {
    $dashboardLink = 'dashboard_admin.php';
    $perfilLink    = 'system_settings.php';
} elseif ($user_role === 'profesor') {
    $dashboardLink = 'dashboard_profesor.php';
    $perfilLink    = 'perfil_profesor.php';
} else {
    $dashboardLink = 'dashboard_estudiante.php';
    $perfilLink    = 'perfil_estudiante.php';
}

// 1) Eliminar pregunta (solo admin)
if (isset($_GET['delete_q']) && $user_role === 'admin') {
    $qid = (int)$_GET['delete_q'];
    $mysqli->query("DELETE FROM forum_question_likes WHERE question_id=$qid");
    $mysqli->query("DELETE FROM forum_answers            WHERE question_id=$qid");
    $mysqli->query("DELETE FROM forum_questions          WHERE id=$qid");
    header("Location: forum.php");
    exit;
}

// 2) Toggle “like”
if (isset($_GET['like_question'])) {
    $qid = (int)$_GET['like_question'];
    $chk = $mysqli->query("SELECT 1 FROM forum_question_likes WHERE question_id=$qid AND user_id=$user_id");
    if ($chk->num_rows) {
        $mysqli->query("DELETE FROM forum_question_likes WHERE question_id=$qid AND user_id=$user_id");
    } else {
        $mysqli->query("INSERT INTO forum_question_likes (question_id,user_id) VALUES ($qid,$user_id)");
    }
    header("Location: forum.php");
    exit;
}

// 3) Publicar pregunta (solo estudiante)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='post_question') {
    if ($user_role !== 'estudiante') {
        $mensaje = "Solo los estudiantes pueden publicar preguntas.";
    } else {
        $title   = $mysqli->real_escape_string($_POST['title']);
        $content = $mysqli->real_escape_string($_POST['content']);
        $imgs    = [];
        $upDir   = __DIR__ . "/../uploads/forum/";
        if (!is_dir($upDir)) mkdir($upDir,0777,true);

        // Guardar imágenes
        if (!empty($_FILES['question_images']['name'][0])) {
            foreach ($_FILES['question_images']['name'] as $i => $name) {
                if ($_FILES['question_images']['error'][$i]===UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg','jpeg','png','gif'])) {
                        $new = "{$user_id}_".time()."_{$i}.{$ext}";
                        move_uploaded_file($_FILES['question_images']['tmp_name'][$i], "$upDir$new");
                        $imgs[] = $new;
                    }
                }
            }
        }
        $imgsDB = $imgs ? "'".implode(',',$imgs)."'" : "NULL";

        // Insertar pregunta
        if ($mysqli->query("
            INSERT INTO forum_questions (user_id,title,content,image)
            VALUES ($user_id,'$title','$content',$imgsDB)
        ")) {
            // Insertar notificación para cada prof/admin
            $qid  = $mysqli->insert_id;
            $list = $mysqli->query("SELECT id FROM users WHERE rol IN ('profesor','admin')");
            while ($u = $list->fetch_assoc()) {
                $uid2 = (int)$u['id'];
                $mysqli->query("
                  INSERT INTO notifications (user_id,question_id)
                  VALUES ($uid2, $qid)
                ");
            }
            $mensaje = "Pregunta publicada correctamente.";
        } else {
            $mensaje = "Error al publicar: ".$mysqli->error;
        }
    }
}

// 4) Publicar respuesta (solo prof/admin)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='post_answer') {
    if (!in_array($user_role,['profesor','admin'])) {
        $mensaje = "Solo profesores o administradores pueden responder.";
    } else {
        $qid  = (int)$_POST['question_id'];
        $cont = $mysqli->real_escape_string($_POST['answer_content']);
        if ($mysqli->query("
            INSERT INTO forum_answers (question_id,user_id,content)
            VALUES ($qid,$user_id,'$cont')
        ")) {
            $mensaje = "Respuesta publicada correctamente.";
        } else {
            $mensaje = "Error al responder: ".$mysqli->error;
        }
    }
}

// 5) Recuperar todas las preguntas
$result_questions = $mysqli->query("
    SELECT fq.id, fq.title, fq.content, fq.created_at, fq.image,
           u.nombre_completo AS author,
           (SELECT COUNT(*) FROM forum_question_likes fql WHERE fql.question_id=fq.id) AS likes,
           (SELECT COUNT(*) FROM forum_question_likes fql WHERE fql.question_id=fq.id AND fql.user_id=$user_id) AS liked_by_user
      FROM forum_questions fq
      JOIN users u ON fq.user_id = u.id
     ORDER BY fq.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Foro de Preguntas</title>
  <link rel="stylesheet" href="../css/forum.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* === Notificaciones === */
    .notif-container { position: relative; margin-left:16px; }
    .notif-btn {
      background:none; border:none; color:#fff; font-size:18px;
      cursor:pointer; position:relative;
    }
    #notifBadge {
      position:absolute; top:-6px; right:-6px;
      background:red; color:#fff; padding:2px 6px;
      border-radius:50%; font-size:0.75em;
    }
    .notif-dropdown {
      display:none; position:absolute; right:0; top:30px;
      width:260px; max-height:300px; overflow-y:auto;
      background:#fff; border:1px solid #ccc;
      box-shadow:0 2px 6px rgba(0,0,0,0.15); z-index:200;
    }
    .notif-dropdown.active { display:block; }
    .notif-item {
      padding:8px 12px; border-bottom:1px solid #eee;
      font-size:0.9em; cursor:pointer;
    }
    .notif-item:last-child { border-bottom:none; }
    .notif-item:hover { background:#f9f9f9; }

    /* === Eliminar pregunta (admin) === */
    .btn-delete {
      background:#dc3545; color:#fff; padding:5px 10px;
      border-radius:4px; text-decoration:none;
      font-size:0.85em; margin-left:8px;
      transition:background .3s;
    }
    .btn-delete:hover { background:#c82333; }
    .question { position:relative; }
    .delete-wrapper { position:absolute; top:16px; right:16px; }
  </style>
</head>
<body>

  <!-- BARRA SUPERIOR -->
  <header class="top-bar">
    <div class="top-bar-left">
      <a href="<?= $dashboardLink ?>">
        <img src="../images/logo_final_superior_imagen_texto_blanco.png" class="logo" alt="Talento+">
      </a>
    </div>
    <div class="slogan">Impulsa tu talento, transforma tu futuro</div>
    <div class="top-bar-right">

      <?php if (in_array($user_role, ['profesor','admin'])): ?>
      <!-- Icono de notificaciones -->
      <div class="notif-container">
        <button id="notifBtn" class="notif-btn">
          <i class="fa fa-bell"></i>
          <span id="notifBadge">0</span>
        </button>
        <div id="notifDropdown" class="notif-dropdown">
          <div class="notif-item">Cargando…</div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Menú y nombre -->
      <input type="checkbox" id="toggleMenu" class="toggle-menu">
      <label for="toggleMenu" class="hamburger"><i class="fa fa-bars"></i></label>
      <nav class="slide-menu">
        <ul>
          <li class="menu-header">
            <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
          </li>
          <li><a href="<?= $perfilLink ?>"><i class="fa fa-user"></i> Mi Perfil</a></li>
          <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
        </ul>
      </nav>
      <span class="username"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
    </div>
  </header>

  <!-- CONTENIDO -->
  <main class="layout-container">

    <!-- ▬▬▬ Zona verde: Publicar + Listado de preguntas ▬▬▬ -->
    <section class="box-green">
      <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <?php if ($user_role==='estudiante'): ?>
      <div class="new-question">
        <h2>Publica tu pregunta</h2>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="post_question">
          <input type="text"    name="title"            placeholder="Título" required>
          <textarea name="content" rows="3" placeholder="Tu pregunta..." required></textarea>
          <input type="file"    name="question_images[]" accept="image/*" multiple>
          <button type="submit">Enviar</button>
        </form>
      </div>
      <?php endif; ?>

      <div class="questions">
        <?php if ($result_questions->num_rows): ?>
          <?php while ($q = $result_questions->fetch_assoc()): ?>
            <div class="question">
              <?php if ($user_role==='admin'): ?>
                <div class="delete-wrapper">
                  <a href="forum.php?delete_q=<?= $q['id'] ?>"
                     class="btn-delete"
                     onclick="return confirm('¿Eliminar pregunta?');">
                    Eliminar
                  </a>
                </div>
              <?php endif; ?>

              <h3><?= htmlspecialchars($q['title']) ?></h3>
              <p><?= nl2br(htmlspecialchars($q['content'])) ?></p>

              <?php if ($q['image']): foreach (explode(',',$q['image']) as $img): ?>
                <img src="../uploads/forum/<?= htmlspecialchars($img) ?>"
                     class="question-img" alt="">
              <?php endforeach; endif; ?>

              <p class="info">
                Por <?= htmlspecialchars($q['author']) ?>
                el <?= $q['created_at'] ?>
              </p>

              <a href="forum.php?like_question=<?= $q['id'] ?>"
                 class="like-btn<?= $q['liked_by_user']?' liked':'' ?>">
                <i class="fa <?= $q['liked_by_user']?'fa-heart':'fa-heart-o' ?>"></i>
                <span class="like-count"><?= $q['likes'] ?></span>
              </a>

              <!-- Respuestas -->
              <?php
                $ans = $mysqli->query("
                  SELECT fa.content,fa.created_at,u.nombre_completo AS answerer
                    FROM forum_answers fa
                    JOIN users u ON fa.user_id=u.id
                   WHERE fa.question_id={$q['id']}
                   ORDER BY fa.created_at
                ");
                while ($a=$ans->fetch_assoc()):
              ?>
                <div class="answer">
                  <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                  <p class="info">
                    Respondió <?= htmlspecialchars($a['answerer']) ?>
                    el <?= $a['created_at'] ?>
                  </p>
                </div>
              <?php endwhile; ?>

              <!-- Formulario de respuesta -->
              <?php if (in_array($user_role,['profesor','admin'])): ?>
              <div class="new-answer">
                <form method="POST">
                  <input type="hidden" name="action"        value="post_answer">
                  <input type="hidden" name="question_id"   value="<?= $q['id'] ?>">
                  <textarea name="answer_content" rows="2" placeholder="Tu respuesta..." required></textarea>
                  <button type="submit">Responder</button>
                </form>
              </div>
              <?php endif; ?>

            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p><em>No hay preguntas publicadas.</em></p>
        <?php endif; ?>
      </div>
    </section>

    <!-- ▬▬▬ Zona amarilla: Banner ▬▬▬ -->
    <section class="box-yellow">
      <div class="banner">
        <img src="../images/banner_para_foro.png" alt="Banner Foro">
      </div>
    </section>

  </main>

  <?php if (in_array($user_role, ['profesor','admin'])): ?>
  <!-- JS: polling cada 5s y dropdown de notificaciones -->
  <script>
    const badge    = document.getElementById('notifBadge');
    const dropdown = document.getElementById('notifDropdown');
    const btn      = document.getElementById('notifBtn');

    btn.addEventListener('click', ()=>{
      dropdown.classList.toggle('active');
    });

    async function fetchNotifications(){
      try {
        let res   = await fetch('notifications.php');
        let notes = await res.json();

        badge.textContent = notes.length;
        if (!notes.length) {
          dropdown.innerHTML = '<div class="notif-item">Sin nuevas preguntas</div>';
        } else {
          dropdown.innerHTML = notes.map(n=>`
            <div class="notif-item" data-id="${n.id}">
              <strong>${n.question_title}</strong><br>
              <small>${n.created_at}</small>
            </div>
          `).join('');
        }
      } catch(e){
        console.error('Error al cargar notificaciones',e);
      }
    }

    dropdown.addEventListener('click', async e=>{
      let item = e.target.closest('.notif-item');
      if (!item || !item.dataset.id) return;
      await fetch('notifications.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'mark_read='+encodeURIComponent(item.dataset.id)
      });
      fetchNotifications();
    });

    setInterval(fetchNotifications,5000);
    fetchNotifications();
  </script>
  <?php endif; ?>
</body>
</html>
