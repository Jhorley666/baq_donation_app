<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: autorizacion.php?id='.$_POST['donante_id'].'&error=Método no permitido');
    exit();
}

$donante_id = intval($_POST['donante_id']);

// Validar archivo subido
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    header('Location: autorizacion.php?id='.$donante_id.'&error=Error al subir archivo');
    exit();
}

// Validar tipo de archivo
$file_type = $_FILES['archivo']['type'];
if ($file_type !== 'application/pdf') {
    header('Location: autorizacion.php?id='.$donante_id.'&error=Solo se permiten archivos PDF');
    exit();
}

// Mover archivo a directorio seguro
$upload_dir = 'autorizaciones_firmadas/';
$file_name = 'autorizacion_firmada_'.$donante_id.'.pdf';
$file_path = $upload_dir.$file_name;

if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $file_path)) {
    header('Location: autorizacion.php?id='.$donante_id.'&error=Error al guardar archivo');
    exit();
}

// Actualizar base de datos
try {
    $stmt = $pdo->prepare("UPDATE donantes SET autorizacion_firmada = ?, fecha_autorizacion = NOW() WHERE id = ?");
    $stmt->execute([$file_path, $donante_id]);
    
    header('Location: autorizacion.php?id='.$donante_id.'&success=Autorización guardada exitosamente');
    
} catch (PDOException $e) {
    header('Location: autorizacion.php?id='.$donante_id.'&error='.urlencode($e->getMessage()));
}