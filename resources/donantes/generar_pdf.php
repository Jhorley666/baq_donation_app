<?php
require_once 'includes/db.php';
require_once 'tcpdf/tcpdf.php';

if (!isset($_GET['id'])) {
    header('Location: registro.php');
    exit();
}

$donante_id = intval($_GET['id']);

// Obtener datos del donante
try {
    $stmt = $pdo->prepare("SELECT * FROM donantes WHERE id = ?");
    $stmt->execute([$donante_id]);
    $donante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$donante) {
        header('Location: registro.php?error=Donante no encontrado');
        exit();
    }
    
    // Generar PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configuración
    $pdf->SetCreator('Banco de Alimentos');
    $pdf->SetAuthor('Sistema de Donaciones');
    $pdf->SetTitle('Autorización de Débito Bancario');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Primera página con datos
    $pdf->AddPage();
    $html = '
    <h1 style="text-align:center;">AUTORIZACIÓN DE DÉBITO BANCARIO</h1>
    <p style="text-align:right;">Fecha: '.date('d/m/Y').'</p>
    
    <p>Yo, <strong>'.$donante['nombres'].' '.$donante['apellidos'].'</strong>, 
    con cédula de identidad No. <strong>'.$donante['cedula'].'</strong>, 
    autorizo a <strong>BANCO DE ALIMENTOS QUITO</strong> para que realice débitos automáticos 
    en mi cuenta bancaria con los siguientes datos:</p>
    
    <table border="1" cellpadding="5">
        <tr>
            <td><strong>Banco:</strong></td>
            <td>'.$donante['banco'].'</td>
        </tr>
        <tr>
            <td><strong>Tipo de Cuenta:</strong></td>
            <td>'.$donante['tipo_cuenta'].'</td>
        </tr>
        <tr>
            <td><strong>Número de Cuenta:</strong></td>
            <td>'.$donante['numero_cuenta'].'</td>
        </tr>
        <tr>
            <td><strong>Monto a Donar:</strong></td>
            <td>$'.number_format($donante['monto_donar'], 2).'</td>
        </tr>
    </table>
    
    <p style="margin-top:50px;">_________________________<br>
    <strong>Firma del Titular</strong></p>
    
    <p style="margin-top:30px;">Fecha: _________________________</p>
    ';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Salida para descarga
    $pdf->Output('autorizacion_firma_manual_'.$donante['cedula'].'.pdf', 'D');
    
} catch (Exception $e) {
    header('Location: registro.php?error='.urlencode('Error al generar PDF: '.$e->getMessage()));
}
?>