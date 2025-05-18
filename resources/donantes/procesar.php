<?php
require_once 'includes/db.php';

// Verificar si TCPDF existe
if (!file_exists('tcpdf/tcpdf.php')) {
    die('Error: La librería TCPDF no está instalada correctamente');
}
require_once 'tcpdf/tcpdf.php';

// Función mejorada para redirigir
function redirigir($url, $error = null) {
    if ($error) {
        header('Location: registro.php?error='.urlencode($error));
    } else {
        header('Location: '.$url);
    }
    exit();
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('registro.php', 'Método no permitido');
}

// Validar campos obligatorios
$campos_requeridos = ['cedula', 'nombres', 'apellidos', 'fecha_nacimiento'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        redirigir('registro.php', "El campo $campo es requerido");
    }
}

// Sanitizar datos
$datos = [
    'cedula' => trim($_POST['cedula']),
    'nombres' => trim($_POST['nombres']),
    'apellidos' => trim($_POST['apellidos']),
    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
    'condicion_cedula' => $_POST['condicion_cedula'] ?? null,
    'lugar_expedicion' => $_POST['lugar_expedicion'] ?? null,
    'fecha_expedicion' => $_POST['fecha_expedicion'] ?? null,
    'telefono' => $_POST['telefono'] ?? null,
    'correo_electronico' => $_POST['correo_electronico'] ?? null,
    'banco' => $_POST['banco'] ?? null,
    'tipo_cuenta' => $_POST['tipo_cuenta'] ?? null,
    'numero_cuenta' => $_POST['numero_cuenta'] ?? null,
    'monto_donar' => !empty($_POST['monto_donar']) ? floatval($_POST['monto_donar']) : null
];

// Validar cédula única
try {
    $stmt = $pdo->prepare("SELECT id FROM donantes WHERE cedula = ?");
    $stmt->execute([$datos['cedula']]);
    if ($stmt->fetch()) {
        redirigir('registro.php', 'La cédula ya está registrada');
    }
} catch (PDOException $e) {
    redirigir('registro.php', 'Error al verificar cédula: '.$e->getMessage());
}

// Insertar en la base de datos
try {
    $sql = "INSERT INTO donantes (
        cedula, nombres, apellidos, fecha_nacimiento, condicion_cedula, 
        lugar_expedicion, fecha_expedicion, telefono, correo_electronico,
        banco, tipo_cuenta, numero_cuenta, monto_donar
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $datos['cedula'], $datos['nombres'], $datos['apellidos'], $datos['fecha_nacimiento'],
        $datos['condicion_cedula'], $datos['lugar_expedicion'], $datos['fecha_expedicion'],
        $datos['telefono'], $datos['correo_electronico'], $datos['banco'], $datos['tipo_cuenta'],
        $datos['numero_cuenta'], $datos['monto_donar']
    ]);

    $donante_id = $pdo->lastInsertId();

    if (!$donante_id) {
        redirigir('registro.php', 'No se pudo obtener el ID del donante');
    }

    // Generar PDF
    $pdf_generado = generarPDF($donante_id, $datos);
    
    if ($pdf_generado) {
        header("Location: autorizacion.php?id=$donante_id");
        exit();
    } else {
        redirigir('registro.php', 'Registro exitoso pero no se pudo generar el PDF');
    }

} catch (PDOException $e) {
    redirigir('registro.php', 'Error al registrar: '.$e->getMessage());
}

// Función mejorada para generar PDF
function generarPDF($donante_id, $datos) {
    try {
        // Verificar y crear directorio si no existe
        $pdf_dir = __DIR__.'/autorizaciones';
        if (!file_exists($pdf_dir)) {
            if (!mkdir($pdf_dir, 0755, true)) {
                error_log("No se pudo crear el directorio para PDFs");
                return false;
            }
        }

        // Crear instancia TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configurar documento
        $pdf->SetCreator('Banco de Alimentos');
        $pdf->SetAuthor('Sistema de Donaciones');
        $pdf->SetTitle('Autorización de Débito Bancario');
        $pdf->SetSubject('Autorización de Donación');

        // Eliminar header/footer por defecto
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Agregar página
        $pdf->AddPage();

        // Contenido HTML
        $html = '
        <h1 style="text-align:center;">AUTORIZACIÓN DE DÉBITO BANCARIO</h1>
        <p style="text-align:right;">Fecha: '.date('d/m/Y').'</p>
        
        <p>Yo, <strong>'.$datos['nombres'].' '.$datos['apellidos'].'</strong>, 
        con cédula de identidad No. <strong>'.$datos['cedula'].'</strong>, 
        autorizo a <strong>BANCO DE ALIMENTOS QUITO</strong> para que realice débitos automáticos 
        en mi cuenta bancaria con los siguientes datos:</p>
        
        <table border="1" cellpadding="5">
            <tr>
                <td><strong>Banco:</strong></td>
                <td>'.$datos['banco'].'</td>
            </tr>
            <tr>
                <td><strong>Tipo de Cuenta:</strong></td>
                <td>'.$datos['tipo_cuenta'].'</td>
            </tr>
            <tr>
                <td><strong>Número de Cuenta:</strong></td>
                <td>'.$datos['numero_cuenta'].'</td>
            </tr>
            <tr>
                <td><strong>Monto a Donar:</strong></td>
                <td>$'.number_format($datos['monto_donar'], 2).'</td>
            </tr>
        </table>
        
        <p style="margin-top:50px;">_________________________<br>
        <strong>Firma del Titular</strong></p>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Ruta del archivo (usando rutas absolutas)
        $pdf_path = $pdf_dir.'/autorizacion_'.$donante_id.'.pdf';

        // Guardar PDF
        $pdf->Output($pdf_path, 'F');

        // Verificar que el archivo se creó
        return file_exists($pdf_path);

    } catch (Exception $e) {
        error_log("Error al generar PDF: ".$e->getMessage());
        return false;
    }
}