<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['rol']; // “estudiante”, “profesor”, “admin”
$mensaje   = "";

// Definir enlaces según rol
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

// Procesar “like”
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['like_question'])) {
    $qid = (int)$_GET['like_question'];
    $chk = $mysqli->query("SELECT * FROM forum_question_likes 
                             WHERE question_id=$qid AND user_id=$user_id");
    if ($chk->num_rows) {
        $mysqli->query("DELETE FROM forum_question_likes 
                          WHERE question_id=$qid AND user_id=$user_id");
    } else {
        $mysqli->query("INSERT INTO forum_question_likes (question_id,user_id)
                          VALUES ($qid,$user_id)");
    }
    header("Location: forum.php");
    exit;
}

// Procesar nueva pregunta (solo estudiantes)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='post_question') {
    if ($user_role!=='estudiante') {
        $mensaje = "Solo estudiantes pueden publicar preguntas.";
    } else {
        $title = $mysqli->real_escape_string($_POST['title']);
        $content = $mysqli->real_escape_string($_POST['content']);
        $imgs = [];
        $upDir = __DIR__."/../uploads/forum/";
        if(!is_dir($upDir)) mkdir($upDir,0777,true);
        if (!empty($_FILES['question_images']['name'][0])) {
            for($i=0;$i<count($_FILES['question_images']['name']);$i++){
                if($_FILES['question_images']['error'][$i]===UPLOAD_ERR_OK){
                    $ext = pathinfo($_FILES['question_images']['name'][$i], PATHINFO_EXTENSION);
                    if(in_array(strtolower($ext),['jpg','jpeg','png','gif'])){
                        $new = "{$user_id}_".time()."_{$i}.{$ext}";
                        move_uploaded_file($_FILES['question_images']['tmp_name'][$i],"$upDir$new")
                            and $imgs[] = $new;
                    }
                }
            }
        }
        $imgsDB = $imgs? "'".implode(',',$imgs)."'" : "NULL";
        if($mysqli->query("
            INSERT INTO forum_questions (user_id,title,content,image)
            VALUES ($user_id,'$title','$content',$imgsDB)
        ")) {
            $mensaje = "Pregunta publicada correctamente.";
        } else {
            $mensaje = "Error: ".$mysqli->error;
        }
    }
}

// Procesar nueva respuesta (solo profes/admin)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='post_answer') {
    if(!in_array($user_role,['profesor','admin'])){
        $mensaje = "Solo profesores/admin pueden responder.";
    } else {
        $qid   = (int)$_POST['question_id'];
        $cont  = $mysqli->real_escape_string($_POST['answer_content']);
        if($mysqli->query("
            INSERT INTO forum_answers (question_id,user_id,content)
            VALUES ($qid,$user_id,'$cont')
        ")) {
            $mensaje = "Respuesta publicada.";
        } else {
            $mensaje = "Error: ".$mysqli->error;
        }
    }
}

// Traer preguntas
$qs = $mysqli->query("
    SELECT fq.id,fq.title,fq.content,fq.created_at,fq.image,
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
</head>
<body>

  <!-- ─── BARRA SUPERIOR (sin categorías ni buscador) ─── -->
  <header class="top-bar">
    <div class="top-bar-left">
      <a href="<?php echo $dashboardLink ?>"><img src="../images/logo_final_superior_imagen_texto_blanco.png" class="logo" alt="Talento+"></a>
    </div>
    <div class="slogan">Impulsa tu talento, transforma tu futuro</div>
    <div class="top-bar-right">
      <input type="checkbox" id="toggleMenu" class="toggle-menu">
      <label for="toggleMenu" class="hamburger"><i class="fa fa-bars"></i></label>
      <nav class="slide-menu">
        <ul>
          <li class="menu-header">
            <img src="../uploads/<?=$_SESSION['foto']?:'default-avatar.png'?>" class="avatar-sm">
            <strong><?=$_SESSION['nombre']?></strong>
          </li>
          <li><a href="<?php echo $perfilLink ?>"><i class="fa fa-user"></i> Mi Perfil</a></li>
          <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
        </ul>
      </nav>
      <img src="../uploads/<?=$_SESSION['foto']?:'default-avatar.png'?>" class="avatar">
      <span class="username"><?=$_SESSION['nombre']?></span>
    </div>
  </header>

  <!-- ─── MAIN EN TRES COLUMNAS ─── -->
  <main class="layout-container">

    <!-- ── VERDE: Publicar + Listado de preguntas ── -->
    <div class="box-green">
      <?php if($mensaje):?>
        <div class="mensaje"><?=$mensaje?></div>
      <?php endif?>

      <?php if($user_role==='estudiante'):?>
      <section class="new-question">
        <h2>Publica tu pregunta</h2>
        <form action="forum.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="post_question">
          <input type="text" name="title" placeholder="Título" required>
          <textarea name="content" rows="3" placeholder="¿Cuál es tu duda?" required></textarea>
          <input type="file" name="question_images[]" accept="image/*" multiple>
          <button type="submit">Enviar</button>
        </form>
      </section>
      <?php endif?>

      <section class="questions">
        <?php if($qs && $qs->num_rows>0): ?>
          <?php while($q = $qs->fetch_assoc()): ?>
            <div class="question">
              <h3><?=$q['title']?></h3>
              <p><?=nl2br($q['content'])?></p>
              <?php if($q['image']): ?>
                <?php foreach(explode(',',$q['image']) as $img): ?>
                  <img src="../uploads/forum/<?=$img?>" class="question-img">
                <?php endforeach?>
              <?php endif?>
              <p class="info">Por <?=$q['author']?> el <?=$q['created_at']?></p>
              <a href="?like_question=<?=$q['id']?>" class="like-btn<?=($q['liked_by_user']?' liked':'')?>">
                <i class="fa <?= $q['liked_by_user']?'fa-heart':'fa-heart-o' ?>"></i>
                <span class="like-count"><?=$q['likes']?></span>
              </a>

              <!-- respuestas -->
              <?php
                $ans = $mysqli->query("
                  SELECT fa.content,fa.created_at,u.nombre_completo AS answerer
                  FROM forum_answers fa
                  JOIN users u ON fa.user_id = u.id
                  WHERE fa.question_id={$q['id']}
                  ORDER BY fa.created_at
                ");
                while($a=$ans->fetch_assoc()):
              ?>
                <div class="answer">
                  <p><?=nl2br($a['content'])?></p>
                  <p class="info">Respondió <?=$a['answerer']?> el <?=$a['created_at']?></p>
                </div>
              <?php endwhile?>

              <!-- nuevo answer (prof/admin) -->
              <?php if(in_array($user_role,['profesor','admin'])):?>
              <div class="new-answer">
                <form action="forum.php" method="POST">
                  <input type="hidden" name="action" value="post_answer">
                  <input type="hidden" name="question_id" value="<?=$q['id']?>">
                  <textarea name="answer_content" rows="2" placeholder="Tu respuesta..." required></textarea>
                  <button type="submit">Responder</button>
                </form>
              </div>
              <?php endif?>

            </div>
          <?php endwhile?>
        <?php else: ?>
          <p><em>No hay preguntas aún.</em></p>
        <?php endif?>
      </section>
    </div>

    <!-- ── AMARILLO: Banner central ── -->
    <div class="box-yellow">
      <div class="banner">
        <img src="../images/banner_para_foro.png" alt="Banner Foro">
      </div>
    </div>

  </main>

</body>
</html>
