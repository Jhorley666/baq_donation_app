<?php
require_once 'tcpdf/tcpdf.php';
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->Write(0, 'Prueba de TCPDF');
$pdf->Output('test.pdf', 'I');