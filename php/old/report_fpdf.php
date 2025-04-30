<?php
// report_fpdf.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}
require '../php/config.php';
require '../vendor/fpdf186/fpdf.php';

$response_id = isset($_GET['response_id'])
             ? (int)$_GET['response_id']
             : 0;
if (!$response_id) die("Falta response_id");

// 1) Metadatos
$sql = "
  SELECT r.created_at,
         t.title AS tpl_title,
         u.nombre_completo,
         u.apellidos
    FROM evaluation_responses r
    JOIN evaluation_templates t ON t.id = r.template_id
    JOIN users u               ON u.id = r.professor_id
   WHERE r.id = $response_id
";
$meta = $mysqli->query($sql)->fetch_assoc();

// 2) Preguntas + respuestas
$sql = "
  SELECT q.text AS question_text,
         a.answer_value
    FROM evaluation_answers a
    JOIN evaluation_questions q ON q.id = a.question_id
   WHERE a.response_id = $response_id
ORDER BY q.qorder ASC
";
$qr = $mysqli->query($sql);

// 3) Creo PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10, utf8_decode('Informe de Autoevaluación'), 0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6, "Estudiante: " 
    . utf8_decode($meta['nombre_completo'].' '.$meta['apellidos']), 0,1);
$pdf->Cell(0,6, "Fecha: " . $meta['created_at'], 0,1);
$pdf->Ln(8);

$pdf->SetFont('Arial','',11);
$total = 0; $count = 0;
while($row = $qr->fetch_assoc()) {
    // Pregunta
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(0,6, utf8_decode($row['question_text']), 0, 'L');
    // Respuesta
    $val = (int)$row['answer_value'];
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(10);
    $pdf->Cell(0,6, "→ $val", 0,1);
    $pdf->Ln(2);

    $total += $val;
    $count++;
}
if ($count) {
  $avg = round($total/$count, 2);
  $pdf->Ln(5);
  $pdf->SetFont('Arial','B',12);
  $pdf->Cell(0,6, "Promedio: $avg", 0,1);
}

$pdf->Output('I', 'evaluacion_'.$response_id.'.pdf');
exit;
