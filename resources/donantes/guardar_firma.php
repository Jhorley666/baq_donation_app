<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['donante_id']) || !isset($data['firma'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$donante_id = intval($data['donante_id']);
$firma = $data['firma'];

try {
    // Guardar firma en la base de datos
    $stmt = $pdo->prepare("UPDATE donantes SET firma_electronica = ?, fecha_firma = NOW() WHERE id = ?");
    $stmt->execute([$firma, $donante_id]);
    
    // También puedes guardar la imagen de la firma en el servidor
    $firma_path = 'firmas/firma_'.$donante_id.'.png';
    file_put_contents($firma_path, file_get_contents($firma));
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}