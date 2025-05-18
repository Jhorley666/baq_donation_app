<?php
require_once 'includes/db.php';
require_once 'tcpdf/tcpdf.php';

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~-+~', '-', $text);
    return empty($text) ? 'n-a' : $text;
}

date_default_timezone_set('America/Guayaquil');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: autorizacion.php?id='.$_POST['donante_id'].'&error=Método no permitido');
    exit();
}

$donante_id = intval($_POST['donante_id']);

try {
    // Obtener datos del donante
    $stmt = $pdo->prepare("SELECT * FROM donantes WHERE id = ?");
    $stmt->execute([$donante_id]);
    $donante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$donante) {
        header('Location: registro.php?error=Donante no encontrado');
        exit();
    }
    
    // Crear directorio para fotos (usando ruta absoluta)
    $subdir = __DIR__.'/autorizaciones/fotos/'.slugify($donante['nombres'].'_'.$donante['cedula']).'/';
    if (!file_exists($subdir)) {
        mkdir($subdir, 0755, true);
    }
    
    // Procesar fotos
    $fotos = ['foto_cedula' => 'Foto de Cédula', 'foto_persona' => 'Foto con Cédula'];
    $imagenes = [];
    
    foreach ($fotos as $campo => $descripcion) {
        if (empty($_FILES[$campo]['tmp_name'])) {
            header('Location: autorizacion.php?id='.$donante_id.'&error='.$descripcion.' es requerida');
            exit();
        }
        
        $tipo = mime_content_type($_FILES[$campo]['tmp_name']);
        if (!in_array($tipo, ['image/jpeg', 'image/png'])) {
            header('Location: autorizacion.php?id='.$donante_id.'&error=Formato no válido para '.$descripcion);
            exit();
        }
        
        $ext = $tipo == 'image/jpeg' ? '.jpg' : '.png';
        $nombre_archivo = $campo.'_'.time().'_'.$donante['cedula'].$ext;
        $ruta_archivo = $subdir.$nombre_archivo;
        
        if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta_archivo)) {
            header('Location: autorizacion.php?id='.$donante_id.'&error=Error al guardar '.$descripcion);
            exit();
        }
        
        $imagenes[$campo] = $ruta_archivo;
    }
    
    // Generar PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Banco de Alimentos');
    $pdf->SetAuthor('Sistema de Donaciones');
    $pdf->SetTitle('Autorización de Débito Bancario');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Primera página
    $pdf->AddPage();
    $html = '
    <h1 style="text-align:center;">AUTORIZACIÓN DE DÉBITO BANCARIO</h1>
    <p style="text-align:right;">Fecha: '.date('d/m/Y H:i:s').'</p>
    
    <p>Yo, <strong>'.$donante['nombres'].' '.$donante['apellidos'].'</strong>, 
    con cédula No. <strong>'.$donante['cedula'].'</strong>, 
    autorizo a <strong>BANCO DE ALIMENTOS QUITO</strong> para débitos automáticos
    en mi cuenta:</p>
    
    <table border="1" cellpadding="5">
        <tr><td><strong>Banco:</strong></td><td>'.$donante['banco'].'</td></tr>
        <tr><td><strong>Tipo de Cuenta:</strong></td><td>'.$donante['tipo_cuenta'].'</td></tr>
        <tr><td><strong>Número de Cuenta:</strong></td><td>'.$donante['numero_cuenta'].'</td></tr>
        <tr><td><strong>Monto a Donar:</strong></td><td>$'.number_format($donante['monto_donar'], 2).'</td></tr>
    </table>
    
    <p style="color: #555; font-size: 10px; margin-top: 20px;">
        Documento autorizado mediante verificación fotográfica<br>
        Fecha: '.date('d/m/Y H:i:s').'
    </p>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Segunda página con fotos
    $pdf->AddPage();
    $html = '
    <h2 style="text-align:center;">Documentos de Verificación</h2>
    <p style="text-align:center; color: #555; margin-bottom: 20px;">
        Autorización validada mediante fotografías adjuntas
    </p>
    
    <h3>1. Foto de Cédula</h3>
    <img src="'.$imagenes['foto_cedula'].'" style="max-width: 500px; border: 1px solid #ddd; padding: 5px;">
    
    <h3 style="margin-top: 30px;">2. Foto con Cédula</h3>
    <img src="'.$imagenes['foto_persona'].'" style="max-width: 500px; border: 1px solid #ddd; padding: 5px;">';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Guardar PDF (usando ruta absoluta)
    $pdf_filename = 'autorizacion_'.$donante['cedula'].'_'.time().'.pdf';
    $pdf_path = $subdir.$pdf_filename;
    $pdf->Output($pdf_path, 'F');
    
    // Verificar que se creó el archivo
    if (!file_exists($pdf_path)) {
        throw new Exception("No se pudo crear el archivo PDF");
    }
    
    // Guardar ruta relativa en BD
    $ruta_relativa = 'autorizaciones/fotos/'.slugify($donante['nombres'].'_'.$donante['cedula']).'/'.$pdf_filename;
    $stmt = $pdo->prepare("UPDATE donantes SET seguimiento = ?, fecha_autorizacion = NOW(), metodo_autorizacion = 'fotos' WHERE id = ?");
    $stmt->execute([$ruta_relativa, $donante_id]);
    
    // Redirigir
    header('Location: agradecimiento.php?donante_id='.$donante_id.'&metodo=fotos');
    
} catch (Exception $e) {
    error_log("Error: ".$e->getMessage());
    header('Location: autorizacion.php?id='.$donante_id.'&error='.urlencode($e->getMessage()));
}