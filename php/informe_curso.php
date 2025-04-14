<?php
session_start();
require('config.php');         // Asegúrate de que la ruta sea correcta
require('../fpdf186/fpdf.php');      // Ajusta la ruta a fpdf.php según la ubicación de tu carpeta fpdf

if (!isset($_GET['course_id'])) {
    die("No se especificó el ID del curso.");
}

$course_id = (int) $_GET['course_id'];

// 1. Obtener información del curso (incluye nombre y descripción)
$queryCurso = "SELECT nombre, descripcion FROM courses WHERE id = $course_id";
$resultCurso = $mysqli->query($queryCurso);
if (!$resultCurso || $resultCurso->num_rows == 0) {
    die("Curso no encontrado.");
}
$curso = $resultCurso->fetch_assoc();
$nombreCurso = $curso['nombre'];

// 2. Consulta para obtener los estudiantes inscritos y sus notas y fecha (usaremos enrollments.fecha_inscripcion)
$sql = "
    SELECT u.nombre_completo, g.calificacion, e.fecha_inscripcion AS fecha_realizacion
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN grades g ON e.course_id = g.course_id AND e.user_id = g.user_id
    WHERE e.course_id = $course_id
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

// 3. Creación del PDF con FPDF
class PDF extends FPDF {
    // Encabezado de página
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Informe por Curso',0,1,'C');
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

// Título del curso
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Curso: " . $nombreCurso,0,1);
$pdf->Ln(5);

// Encabezados de la tabla
$pdf->SetFont('Arial','B',12);
$pdf->Cell(70,10,'Estudiante',1,0,'C');
$pdf->Cell(40,10,'Calificacion',1,0,'C');
$pdf->Cell(80,10,'Fecha de Realizacion',1,1,'C');

// Rellenamos la tabla con los datos
$pdf->SetFont('Arial','',12);
foreach ($data as $row) {
    $estudiante = !empty($row['nombre_completo']) ? $row['nombre_completo'] : 'N/A';
    $nota       = !empty($row['calificacion']) ? $row['calificacion'] : 'N/A';
    $fecha      = !empty($row['fecha_realizacion']) ? $row['fecha_realizacion'] : 'N/A';
    $pdf->Cell(70,10,$estudiante,1,0);
    $pdf->Cell(40,10,$nota,1,0,'C');
    $pdf->Cell(80,10,$fecha,1,1);
}

// Salida del PDF
$pdf->Output('D', 'informe_curso_'.$course_id.'.pdf');
?>
