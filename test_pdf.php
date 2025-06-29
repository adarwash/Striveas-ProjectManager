<?php
require('app/helpers/fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'Hello World!');
$pdf->Output('F', 'hello_world.pdf');
echo "PDF generated at hello_world.pdf\n";
