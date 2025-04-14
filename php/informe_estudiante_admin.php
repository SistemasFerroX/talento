<?php
session_start();
require('config.php');
require('../fpdf186/fpdf.php');

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

if (!isset($_GET['student_id'])) {
    die("No se especificó el ID del estudiante.");
}

$student_id = (int) $_GET['student_id'];

// Consulta para obtener la información del estudiante
$queryStudent = "SELECT nombre_completo FROM users WHERE id = $student_id AND rol = 'estudiante'";
$resultStudent = $mysqli->query($queryStudent);
if (!$resultStudent || $resultStudent->num_rows == 0) {
    die("Estudiante no encontrado.");
}
$student = $resultStudent->fetch_assoc();
$studentName = $student['nombre_completo'];

// Consulta para obtener los cursos realizados por el estudiante
// Se usa enrollments.fecha_inscripcion para la fecha y un LEFT JOIN a grades para obtener la calificación.
$sql = "
    SELECT c.nombre AS curso, g.calificacion, e.fecha_inscripcion AS fecha_realizacion
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN grades g ON e.course_id = g.course_id AND e.user_id = g.user_id
    WHERE e.user_id = $student_id
    ORDER BY e.fecha_inscripcion ASC
";
$result = $mysqli->query($sql);
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    die("Error en la consulta: " . $mysqli->error);
}

// Clase PDF con FPDF
class PDF extends FPDF {
    // Encabezado
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Informe por Estudiante',0,1,'C');
        $this->Ln(5);
    }
    // Pie de página
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Título del informe
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Estudiante: " . $studentName, 0, 1, 'C');
$pdf->Ln(5);

// Encabezados de la tabla
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,'Curso',1,0,'C');
$pdf->Cell(40,10,'Calificacion',1,0,'C');
$pdf->Cell(70,10,'Fecha de Realizacion',1,1,'C');

// Rellenar la tabla con los datos
$pdf->SetFont('Arial','',12);
foreach ($data as $row) {
    $curso  = !empty($row['curso']) ? $row['curso'] : 'N/A';
    $nota   = !empty($row['calificacion']) ? $row['calificacion'] : 'N/A';
    $fecha  = !empty($row['fecha_realizacion']) ? $row['fecha_realizacion'] : 'N/A';
    $pdf->Cell(80,10, $curso, 1,0);
    $pdf->Cell(40,10, $nota, 1,0,'C');
    $pdf->Cell(70,10, $fecha, 1,1,'C');
}

// Salida del PDF (D para descargar, I para visualizar en navegador)
$pdf->Output('D', 'informe_estudiante_'.$student_id.'.pdf');
?>
