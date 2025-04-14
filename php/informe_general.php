<?php
session_start();
require('config.php');
require('../fpdf186/fpdf.php');

// Verificar admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

// Verificar parámetros
if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
    die("Se deben especificar la fecha de inicio y la fecha de fin.");
}

$fecha_inicio = $mysqli->real_escape_string($_GET['fecha_inicio']);
$fecha_fin    = $mysqli->real_escape_string($_GET['fecha_fin']);

// Consulta
$sql = "
    SELECT u.nombre_completo, 
           c.nombre AS curso,
           g.calificacion,
           e.fecha_inscripcion AS fecha_realizacion
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN grades g ON e.course_id = g.course_id 
                      AND e.user_id = g.user_id
    WHERE e.fecha_inscripcion BETWEEN '$fecha_inicio' AND '$fecha_fin'
    ORDER BY e.fecha_inscripcion ASC
";
$result = $mysqli->query($sql);
if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Clase PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Informe General por Rango de Fechas',0,1,'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Título
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Informe: $fecha_inicio a $fecha_fin",0,1,'C');
$pdf->Ln(5);

// Encabezados
$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,'Estudiante',1,0,'C');
$pdf->Cell(60,10,'Curso',1,0,'C');
$pdf->Cell(30,10,'Calif.',1,0,'C');
$pdf->Cell(40,10,'Fecha',1,1,'C');

// Datos
$pdf->SetFont('Arial','',12);
foreach($data as $row){
    $estudiante = $row['nombre_completo'] ?? 'N/A';
    $curso = $row['curso'] ?? 'N/A';
    $nota = $row['calificacion'] ?? 'N/A';
    $fecha = $row['fecha_realizacion'] ?? 'N/A';

    $pdf->Cell(60,10,$estudiante,1,0);
    $pdf->Cell(60,10,$curso,1,0);
    $pdf->Cell(30,10,$nota,1,0,'C');
    $pdf->Cell(40,10,$fecha,1,1,'C');
}

// Output
$pdf->Output('D', 'informe_general_'.$fecha_inicio.'_a_'.$fecha_fin.'.pdf');
