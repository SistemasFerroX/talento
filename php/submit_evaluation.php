<?php
session_start();
if(!isset($_SESSION['user_id'])||$_SESSION['rol']!=='estudiante'){
  header("Location: ../login.html"); exit;
}
require '../php/config.php';

$user_id  = $_SESSION['user_id'];
$tpl_id   = (int)$_POST['tpl_id'];
$answers  = $_POST['q'] ?? [];

if(!$tpl_id || empty($answers)){
  die("Faltan datos.");
}

// 1) Inserto la cabecera de la respuesta
$stmt = $mysqli->prepare(
  "INSERT INTO evaluation_responses(user_id,template_id) VALUES(?,?)"
);
$stmt->bind_param("ii",$user_id,$tpl_id);
$stmt->execute();
$response_id = $mysqli->insert_id;

// 2) Inserto cada respuesta
$stmt = $mysqli->prepare(
  "INSERT INTO evaluation_answers(response_id,question_id,answer) VALUES(?,?,?)"
);
foreach($answers as $qid => $ans){
  $stmt->bind_param("iis", $response_id, $qid, $ans);
  $stmt->execute();
}

header("Location: evaluation_form.php?tpl=$tpl_id&ok=1");
