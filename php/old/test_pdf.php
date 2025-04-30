<?php
// Asegúrate de ajustar la ruta a fpdf.php según la ubicación de la carpeta fpdf en tu proyecto
require('fpdf186/fpdf.php');

// Crea una nueva instancia de FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, '¡Hola, mundo!');
$pdf->Output();
?>
