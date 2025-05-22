<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$prof_id = $_SESSION['user_id'];
$empresa = $mysqli->real_escape_string($_SESSION['empresa']);

// Avatar
$fotoFile = $_SESSION['foto'] ?? '';
$fotoURL  = ($fotoFile && file_exists(__DIR__ . "/../uploads/$fotoFile"))
            ? '../uploads/' . rawurlencode($fotoFile)
            : '../images/default-avatar.png';

// Cursos del profesor
$cursos = $mysqli->query("
   SELECT id, nombre
     FROM courses
    WHERE profesor_id = $prof_id
      AND empresa      = '$empresa'
    ORDER BY fecha_creacion DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Profesor</title>
  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Notificaciones */
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
  </style>
</head>
<body>
<header class="top-bar">
  <div class="top-bar-left">
    <a href="#"><img src="../images/logo_final_superior_imagen_texto.png" class="logo" alt=""></a>
  </div>
  <div class="slogan">Guía con tu conocimiento, inspira el crecimiento</div>
  <div class="top-bar-right">
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

    <!-- Avatar y menú -->
    <img src="<?= htmlspecialchars($fotoURL) ?>" class="avatar">
    <span class="username"><?= htmlspecialchars($_SESSION['nombre']) ?></span>

    <input type="checkbox" id="toggleMenu" class="toggle-menu">
    <label for="toggleMenu" class="hamburger">
      <i class="fa-solid fa-bars"></i>
    </label>
    <nav class="slide-menu">
      <ul>
        <li class="menu-header">
          <img src="<?= htmlspecialchars($fotoURL) ?>" class="avatar-sm">
          <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
        </li>
        <li><a href="perfil_profesor.php"><i class="fa-regular fa-user"></i> Mi Perfil</a></li>
        <li><a href="forum.php"><i class="fa-regular fa-comments"></i> Foro</a></li>
        <li><a href="create_course.php"><i class="fa fa-graduation-cap"></i> Crear Curso</a></li>
        <li class="divider"></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
      </ul>
    </nav>
  </div>
</header>

<div class="banner-slider">
  <img id="bannerImg" src="../images/RECURSOS_BANNER1.png" alt="Banner">
</div>

<main class="course-list">
  <h2>Mis Cursos</h2>

  <?php if ($cursos && $cursos->num_rows): ?>
    <?php while ($c = $cursos->fetch_assoc()): ?>
      <?php
        // Prepara listas de alumnos dentro y fuera
        $libres  = $mysqli->query("SELECT id,nombre_completo FROM users WHERE empresa='$empresa' AND rol='estudiante' AND id NOT IN (SELECT user_id FROM enrollments WHERE course_id={$c['id']})");
        $dentro  = $mysqli->query("SELECT u.id,u.nombre_completo FROM users u JOIN enrollments e ON e.user_id=u.id WHERE e.course_id={$c['id']}");
      ?>
      <article class="course-item" id="c<?= $c['id'] ?>">
        <h3><?= htmlspecialchars($c['nombre']) ?></h3>

        <div class="course-actions">
          <button class="btn btn-blue abrir" data-panel="add" data-id="<?= $c['id'] ?>">
            Inscribir Estudiantes
          </button>
          <button class="btn btn-dark abrir" data-panel="manage" data-id="<?= $c['id'] ?>">
            Gestionar Estudiantes
          </button>
        </div>

        <!-- Panel: inscribir -->
        <div class="panel hidden" id="add<?= $c['id'] ?>">
          <h4>Estudiantes disponibles</h4>
          <?php if ($libres && $libres->num_rows): ?>
            <?php while ($al = $libres->fetch_assoc()): ?>
              <div class="row">
                <span><?= htmlspecialchars($al['nombre_completo']) ?></span>
                <a class="mini-btn" href="inscribir.php?course=<?= $c['id'] ?>&u=<?= $al['id'] ?>">
                  + Inscribir
                </a>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="empty-msg">Sin candidatos.</p>
          <?php endif; ?>
        </div>

        <!-- Panel: gestionar -->
        <div class="panel hidden" id="manage<?= $c['id'] ?>">
          <h4>Estudiantes inscritos</h4>
          <?php if ($dentro && $dentro->num_rows): ?>
            <?php while ($al = $dentro->fetch_assoc()): ?>
              <div class="row">
                <span><?= htmlspecialchars($al['nombre_completo']) ?></span>
                <a class="mini-btn danger" href="expulsar.php?course=<?= $c['id'] ?>&u=<?= $al['id'] ?>">
                  × Quitar
                </a>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="empty-msg">No hay inscritos.</p>
          <?php endif; ?>
        </div>
      </article>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="empty-msg">Aún no has creado cursos.</p>
  <?php endif; ?>
</main>

<script>
// Slider de banners
const slides = [
  "../images/RECURSOS_BANNER1.png",
  "../images/RECURSOS_BANNER2.png",
  "../images/RECURSOS_BANNER3.png"
];
let idx = 0, banner = document.getElementById("bannerImg");
setInterval(()=>{
  idx = (idx+1) % slides.length;
  banner.style.opacity = 0;
  setTimeout(()=>{
    banner.src = slides[idx];
    banner.style.opacity = 1;
  }, 400);
}, 6000);

// Abrir/Cerrar paneles de inscribir/gestionar
document.querySelectorAll('.abrir').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const panel = document.getElementById(btn.dataset.panel + btn.dataset.id);
    panel.classList.toggle('hidden');
  });
});

// ---- Polling de notificaciones (cada 5s) ----
const badge    = document.getElementById('notifBadge');
const dropdown = document.getElementById('notifDropdown');
const btnNotif = document.getElementById('notifBtn');

btnNotif.addEventListener('click', () => {
  dropdown.classList.toggle('active');
});

async function fetchNotifications() {
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
  } catch(e) {
    console.error('Error al cargar notificaciones', e);
  }
}

// Al hacer click en una notificación, la marcamos como leída
dropdown.addEventListener('click', async e => {
  let item = e.target.closest('.notif-item');
  if (!item || !item.dataset.id) return;
  await fetch('notifications.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'mark_read=' + encodeURIComponent(item.dataset.id)
  });
  fetchNotifications();
});

setInterval(fetchNotifications, 5000);
fetchNotifications();
</script>
</body>
</html>
