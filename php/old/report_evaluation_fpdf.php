<?php
require '../php/config.php';
require '../fpdf/fpdf.php';
session_start();
if(!isset($_SESSION['rol'])||$_SESSION['rol']!=='admin'){
  header("Location: ../login.html"); exit;
}

$tpl_id = (int)$_GET['tpl'];

// Cabecera plantilla
$stmt = $mysqli->prepare("SELECT name FROM evaluation_templates WHERE id=?");
$stmt->bind_param("i",$tpl_id); $stmt->execute();
$tpl = $stmt->get_result()->fetch_assoc();

// Traer preguntas
$stmt = $mysqli->prepare("SELECT id,text FROM evaluation_questions WHERE template_id=?");
$stmt->bind_param("i",$tpl_id); $stmt->execute();
$questions = $stmt->get_result();

// Traer respuestas
$stmt = $mysqli->prepare("
  SELECT r.id,r.user_id,r.created_at,u.nombre_completo
    FROM evaluation_responses r
    JOIN users u ON r.user_id=u.id
   WHERE r.template_id=?
   ORDER BY r.created_at
");
$stmt->bind_param("i",$tpl_id); $stmt->execute();
$responses = $stmt->get_result();

// Instancio FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,utf8_decode("Informe: ".$tpl['name']),0,1,'C');
$pdf->Ln(5);

while($resp=$responses->fetch_assoc()){
  $pdf->SetFont('Arial','B',12);
  $pdf->Cell(0,8,utf8_decode("Respuesta ID {$resp['id']} - Usuario: {$resp['nombre_completo']} ({$resp['created_at']})"),0,1);
  $pdf->SetFont('Arial','',11);

  // Por cada pregunta buscamos la respuesta
  $stmt2 = $mysqli->prepare("
    SELECT a.answer,q.text
      FROM evaluation_answers a
      JOIN evaluation_questions q ON a.question_id=q.id
     WHERE a.response_id=?
  ");
  $stmt2->bind_param("i",$resp['id']);
  $stmt2->execute();
  $ans = $stmt2->get_result();
  while($a = $ans->fetch_assoc()){
    $pdf->MultiCell(0,6,utf8_decode("• ".$a['text'].": ".$a['answer']));
  }
  $pdf->Ln(3);
}

$pdf->Output('I',"evaluacion_{$tpl_id}.pdf");
