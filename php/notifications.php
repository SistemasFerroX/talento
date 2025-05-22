<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

// Solo profesores y admins pueden recibir notificaciones
if (!in_array($_SESSION['rol'], ['profesor','admin'])) {
    http_response_code(403);
    echo json_encode(['error'=>'Acceso denegado']);
    exit;
}

$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    // Marcar notificación como leída
    $nid = (int)$_POST['mark_read'];
    $mysqli->query("
      UPDATE notifications
         SET is_read = 1
       WHERE id = $nid
         AND user_id = $uid
    ");
    exit;
}

// GET: traer no leídas
$res = $mysqli->query("
  SELECT n.id,
         q.title       AS question_title,
         n.created_at
    FROM notifications n
    JOIN forum_questions q ON q.id = n.question_id
   WHERE n.user_id = $uid
     AND n.is_read = 0
   ORDER BY n.created_at DESC
");

$notes = [];
while ($row = $res->fetch_assoc()) {
    $notes[] = $row;
}
echo json_encode($notes);
