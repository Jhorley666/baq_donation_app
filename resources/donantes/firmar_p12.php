<?php
require_once 'includes/db.php';
require_once 'tcpdf/tcpdf.php';

date_default_timezone_set('America/Guayaquil');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: autorizacion.php?id='.$_POST['donante_id'].'&error=Método no permitido');
    exit();
}

$donante_id = intval($_POST['donante_id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM donantes WHERE id = ?");
    $stmt->execute([$donante_id]);
    $donante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$donante) {
        header('Location: registro.php?error=Donante no encontrado');
        exit();
    }

    // Verificar certificado subido
    if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
        header('Location: autorizacion.php?id='.$donante_id.'&error=Error al subir certificado');
        exit();
    }
    
    $certificado_temp = $_FILES['certificado']['tmp_name'];
    $password = $_POST['password'];
    $razon = $_POST['razon'];
    $ubicacion = $_POST['ubicacion'];
    
    // Extraer certificado
    $certificado_data = file_get_contents($certificado_temp);
    $cert_info = [];
    if (!openssl_pkcs12_read($certificado_data, $cert_info, $password)) {
        header('Location: autorizacion.php?id='.$donante_id.'&error=Certificado o contraseña inválidos');
        exit();
    }

    // Crear directorio para autorizaciones (using absolute path)
    $dir_base = __DIR__.'/autorizaciones/';
    if (!file_exists($dir_base)) {
        mkdir($dir_base, 0755, true);
    }

    // Generar PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Banco de Alimentos');
    $pdf->SetAuthor('Sistema de Donaciones');
    $pdf->SetTitle('Autorización de Débito Bancario');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    $pdf->AddPage();
    
    $html = '
    <style>
        .firma-electronica {
            color: #555;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 50px;
            font-style: italic;
        }
        .fecha-firma {
            margin-top: 30px;
        }
    </style>
    
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
    
    <div class="firma-electronica">
        Firmado electrónicamente por:<br>
        <strong>'.$donante['nombres'].' '.$donante['apellidos'].'</strong><br>
        Cédula: '.$donante['cedula'].'<br>
        Fecha: '.date('d/m/Y H:i:s').'
    </div>';
    
    $pdf->writeHTML($html, true, false, true, false, '');

    // Configurar firma
    $pdf->setSignature(
        $cert_info['cert'], 
        $cert_info['pkey'], 
        $password,
        '',
        3,
        [
            'Name' => $donante['nombres'].' '.$donante['apellidos'],
            'Location' => $ubicacion,
            'Reason' => $razon,
            'ContactInfo' => $donante['correo_electronico']
        ],
        'A'
    );

    // Crear subdirectorio para P12 usando ruta absoluta
    $subdir = __DIR__.'/autorizaciones/p12/';
    if (!file_exists($subdir)) {
        mkdir($subdir, 0755, true);
    }

    // Nombre del archivo PDF
    $pdf_filename = 'autorizacion_'.$donante['cedula'].'_'.time().'.pdf';
    $pdf_path = $subdir.$pdf_filename;

    // Guardar PDF usando ruta absoluta
    $pdf->Output($pdf_path, 'F');

    // Guardar ruta relativa en BD
    $ruta_relativa = 'autorizaciones/p12/'.$pdf_filename;
    $stmt = $pdo->prepare("UPDATE donantes SET seguimiento = ?, fecha_autorizacion = NOW(), metodo_autorizacion = 'p12' WHERE id = ?");
    $stmt->execute([$ruta_relativa, $donante_id]);

    // Redirigir a agradecimiento
    header('Location: agradecimiento.php?donante_id='.$donante_id.'&metodo=p12');
    
} catch (Exception $e) {
    error_log("Error en firmar_p12.php: ".$e->getMessage());
    header('Location: autorizacion.php?id='.$donante_id.'&error=Error en el proceso');
}
?>