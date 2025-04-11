<?php
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'domain'   => '',       // O 'localhost' si lo prefieres
  'secure'   => false,    // false, porque usas HTTP, no HTTPS
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

if (!isset($_GET['id'])) {
    die("Curso no especificado.");
}
$curso_id = intval($_GET['id']);

// Ejemplo: Eliminar el curso. Si tienes claves foráneas con ON DELETE CASCADE, se eliminarán los registros relacionados.
$query = "DELETE FROM courses WHERE id = $curso_id";
if ($mysqli->query($query)) {
    header("Location: mis_cursos.php");
    exit;
} else {
    die("Error al eliminar el curso: " . $mysqli->error);
}
?>
