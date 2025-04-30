<?php
// toggle_evaluation.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol']!=='admin') {
  header("Location: ../login.html");
  exit;
}
require __DIR__ . '/config.php';

$template_id = isset($_GET['template_id']) ? (int)$_GET['template_id'] : 0;
if (!$template_id) die("Falta template_id");

// Cambiamos is_active (0⇄1)
$stmt = $mysqli->prepare("
  UPDATE evaluation_templates
     SET is_active = 1 - is_active
   WHERE id = ?
");
$stmt->bind_param("i",$template_id);
$stmt->execute();

// Volvemos al dashboard
header("Location: dashboard_admin.php");
exit;
?>
