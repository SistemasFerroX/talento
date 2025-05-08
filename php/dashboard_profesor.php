<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../login.html"); exit;
}
require 'config.php';

$prof_id = $_SESSION['user_id'];
$empresa = $mysqli->real_escape_string($_SESSION['empresa']);

/* avatar */
$fotoFile = $_SESSION['foto'] ?? '';
$fotoURL  = ( $fotoFile && file_exists(__DIR__."/../uploads/$fotoFile") )
            ? '../uploads/'.rawurlencode($fotoFile)
            : '../images/default-avatar.png';

/* cursos del profesor */
$cursos = $mysqli->query("
   SELECT id,nombre
     FROM courses
    WHERE profesor_id = $prof_id
      AND empresa      = '$empresa'
    ORDER BY fecha_creacion DESC
");

/* función de apoyo: devuelve array con estudiantes */
function alumnos($mysqli,$empresa,$course_id,$yaInscritos=false){
    return $mysqli->query(
        $yaInscritos
        ? "SELECT u.id,u.nombre_completo
             FROM users u
             JOIN enrollments e ON e.user_id=u.id
            WHERE e.course_id=$course_id"
        : "SELECT id,nombre_completo
             FROM users
            WHERE empresa ='$empresa'
              AND rol     ='estudiante'
              AND id NOT IN (SELECT user_id
                                FROM enrollments
                               WHERE course_id=$course_id)"
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Profesor</title>

  <link rel="stylesheet" href="../css/dashboard_profesor.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="top-bar">
  <div class="top-bar-left">
     <a href="#"><img src="../images/logo_final_superior_imagen_texto.png" class="logo" alt=""></a>
  </div>
  <div class="slogan">Guía con tu conocimiento, inspira el crecimiento</div>
  <div class="top-bar-right">
     <img src="<?=htmlspecialchars($fotoURL)?>" class="avatar">
     <span class="username"><?=htmlspecialchars($_SESSION['nombre'])?></span>

     <input type="checkbox" id="toggleMenu" class="toggle-menu">
     <label for="toggleMenu" class="hamburger"><i class="fa-solid fa-bars"></i></label>

     <nav class="slide-menu">
        <ul>
          <li class="menu-header"><img src="<?=htmlspecialchars($fotoURL)?>" class="avatar-sm">
              <strong><?=htmlspecialchars($_SESSION['nombre'])?></strong></li>
          <li><a href="perfil_profesor.php"><i class="fa-regular fa-user"></i> Mi Perfil</a></li>
          <li><a href="forum.php"><i class="fa-regular fa-comments"></i> Foro</a></li>
          <li class="divider"></li>
          <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
     </nav>
  </div>
</header>

<div class="banner-slider"><img id="bannerImg" src="../images/RECURSOS_BANNER1.png"></div>

<main class="course-list">
  <h2>Mis Cursos</h2>

<?php if($cursos && $cursos->num_rows): while($c = $cursos->fetch_assoc()): ?>
<?php
   $libres = alumnos($mysqli,$empresa,$c['id'],false);
   $dentro = alumnos($mysqli,$empresa,$c['id'],true);
?>
  <article class="course-item" id="c<?= $c['id'] ?>">
     <h3><?= htmlspecialchars($c['nombre']) ?></h3>

     <div class="course-actions">
        <button class="btn btn-blue abrir" data-panel="add" data-id="<?= $c['id'] ?>">
           Inscribir Estudiantes
        </button>
        <button class="btn btn-dark abrir" data-panel="manage" data-id="<?= $c['id'] ?>">
           Gestionar Estudiantes
        </button>
     </div>

     <!-- PANEL: inscribir -->
     <div class="panel hidden" id="add<?= $c['id'] ?>">
         <h4>Estudiantes disponibles</h4>
         <?php if($libres && $libres->num_rows): ?>
             <?php while($al=$libres->fetch_assoc()): ?>
                <div class="row">
                   <span><?= htmlspecialchars($al['nombre_completo']) ?></span>
                   <a class="mini-btn"
                      href="inscribir.php?course=<?= $c['id'] ?>&u=<?= $al['id'] ?>">
                      + Inscribir
                   </a>
                </div>
             <?php endwhile; ?>
         <?php else: ?><p class="empty-msg">Sin candidatos.</p><?php endif; ?>
     </div>

     <!-- PANEL: gestionar -->
     <div class="panel hidden" id="manage<?= $c['id'] ?>">
         <h4>Estudiantes inscritos</h4>
         <?php if($dentro && $dentro->num_rows): ?>
             <?php while($al=$dentro->fetch_assoc()): ?>
                <div class="row">
                   <span><?= htmlspecialchars($al['nombre_completo']) ?></span>
                   <a class="mini-btn danger"
                      href="expulsar.php?course=<?= $c['id'] ?>&u=<?= $al['id'] ?>">
                      × Quitar
                   </a>
                </div>
             <?php endwhile; ?>
         <?php else: ?><p class="empty-msg">No hay inscritos.</p><?php endif; ?>
     </div>
  </article>
<?php endwhile; else: ?>
     <p class="empty-msg">Aún no has creado cursos.</p>
<?php endif; ?>
</main>

<script>
/* slider */
const slides=["../images/RECURSOS_BANNER1.png","../images/RECURSOS_BANNER2.png","../images/RECURSOS_BANNER3.png"];
let i=0,img=document.getElementById("bannerImg");
setInterval(()=>{i=(i+1)%slides.length;img.style.opacity=0;
   setTimeout(()=>{img.src=slides[i];img.style.opacity=1;},400);
},6000);

/* abrir / cerrar paneles */
document.querySelectorAll('.abrir').forEach(btn=>{
   btn.addEventListener('click',e=>{
      const id   = btn.dataset.id;
      const tipo = btn.dataset.panel;             // add | manage
      const panel = document.getElementById(tipo+id);
      panel.classList.toggle('hidden');
   });
});
</script>
</body>
</html>
