<?php
// report_eval.php

// 1) Arrancamos buffering y sesión
ob_start();
session_start();

// 2) Sólo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}

// 3) Rutas
require __DIR__ . '/config.php';
require __DIR__ . '/../fpdf186/fpdf.php';

// 4) Parámetro obligatorio
$response_id = isset($_GET['response_id']) ? (int)$_GET['response_id'] : 0;
if (!$response_id) {
    die("Falta el parámetro response_id");
}

// 5) Traer datos generales de esta respuesta
$stmt = $mysqli->prepare("
  SELECT 
    r.created_at,
    t.title            AS plantilla,
    u.nombre_completo  AS evaluador
  FROM evaluation_responses r
  JOIN evaluation_templates t ON r.template_id = t.id
  JOIN users u               ON r.professor_id = u.id
  WHERE r.id = ?
");
$stmt->bind_param("i", $response_id);
$stmt->execute();
$resp = $stmt->get_result()->fetch_assoc();
if (!$resp) {
    die("Respuesta no encontrada");
}

// 6) Traer todas las respuestas (valor + texto) con su pregunta
$stmt = $mysqli->prepare("
  SELECT 
    q.text           AS pregunta,
    q.question_type,
    a.answer_value,
    a.answer_text
  FROM evaluation_answers a
  JOIN evaluation_questions q ON a.question_id = q.id
  WHERE a.response_id = ?
  ORDER BY q.sort_order ASC
");
$stmt->bind_param("i", $response_id);
$stmt->execute();
$rs = $stmt->get_result();

// 7) Calcular promedio sólo de los radiobuttons
$total = 0;
$count = 0;
$answers = [];
while ($row = $rs->fetch_assoc()) {
    $answers[] = $row;
    if ($row['question_type'] === 'radio') {
        $v = (int)$row['answer_value'];
        if ($v >= 1 && $v <= 5) {
            $total += $v;
            $count++;
        }
    }
}
$avg = $count ? number_format($total / $count, 2) : 'N/A';

// 8) Generar PDF
$pdf = new FPDF();
$pdf->AddPage();

// — Inserta el logo centrado en la parte superior —
// Ruta al logo
$logoPath = __DIR__ . '/../images/LogoFerbienes.png';
if (file_exists($logoPath)) {
    // Ancho deseado del logo
    $logoWidth = 40;
    // Coordenada X: (ancho de la página – ancho del logo) / 2
    $x = ($pdf->GetPageWidth() - $logoWidth) / 2;
    // Y fijo a 8 mm desde arriba
    $pdf->Image($logoPath, $x, 8, $logoWidth);
}

// Ajustamos el cursor justo debajo del logo
$pdf->SetY(8 + ($logoWidth * 0.75)); // asumiendo proporción aproximada 4:3

// Título centrado
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode("Evaluación: {$resp['plantilla']}"), 0, 1, 'C');

// Metadatos
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, "Evaluador: " . utf8_decode($resp['evaluador']), 0, 1);
$pdf->Cell(0, 6, "Fecha: "     . $resp['created_at'],       0, 1);
$pdf->Cell(0, 6, "Promedio: "  . $avg,                    0, 1);
$pdf->Ln(8);

// Listado numerado con espacio entre preguntas
$pdf->SetFont('Arial', '', 12);
foreach ($answers as $i => $r) {
    $n = $i + 1;
    $q = utf8_decode($r['pregunta']);
    // Si es radio, usamos answer_value; si no, answer_text
    if ($r['question_type'] === 'radio') {
        $a = $r['answer_value'];
    } else {
        $a = utf8_decode($r['answer_text']);
    }
    $pdf->MultiCell(0, 6, "{$n}. {$q}: {$a}", 0, 'L');
    $pdf->Ln(4);
}

// 9) Enviamos PDF al navegador
$pdf->Output('I', "evaluacion_{$response_id}.pdf");
exit;
