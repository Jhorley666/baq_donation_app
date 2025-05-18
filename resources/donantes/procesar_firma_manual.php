<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit();
}

$donante_id = intval($_POST['donante_id']);

try {
    // Obtener datos del donante
    $stmt = $pdo->prepare("SELECT * FROM donantes WHERE id = ?");
    $stmt->execute([$donante_id]);
    $donante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$donante) {
        throw new Exception("Donante no encontrado");
    }
    
    // Verificar archivo subido
    if (!isset($_FILES['documento_firmado']) || $_FILES['documento_firmado']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error al subir el documento firmado");
    }
    
// Crear subdirectorio para manual si no existe
$subdir = 'autorizaciones/manual/';
if (!file_exists($subdir)) {
    mkdir($subdir, 0755, true);
}

// Generar nombre único para el archivo
$file_ext = pathinfo($_FILES['documento_firmado']['name'], PATHINFO_EXTENSION);
$new_filename = 'autorizacion_'.$donante['cedula'].'_'.time().'.'.$file_ext;
$destination = $subdir.$new_filename;

// Mover el archivo
move_uploaded_file($_FILES['documento_firmado']['tmp_name'], $destination);

// Actualizar BD
$ruta_relativa = $subdir.$new_filename;
$stmt = $pdo->prepare("UPDATE donantes SET seguimiento = ?, fecha_autorizacion = NOW(), metodo_autorizacion = 'manual' WHERE id = ?");
$stmt->execute([$ruta_relativa, $donante_id]);
    
    // Redirigir a agradecimiento
    header('Location: agradecimiento.php?donante_id='.$donante_id.'&metodo=manual');
    
} catch (Exception $e) {
    header('Location: registro.php?error='.urlencode($e->getMessage()));
}
?>