<?php
require_once 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'leerQR' && isset($_GET['url'])) {
    
    $url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
    
    if (!filter_var($url, FILTER_VALIDATE_URL) || !str_contains($url, 'registrocivil.gob.ec/qr')) {
        echo json_encode(['error' => 'URL no válida del Registro Civil']);
        exit;
    }

    // Configurar cURL para obtener los datos
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false, // Solo para desarrollo
        CURLOPT_TIMEOUT => 10
    ]);
    
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Error al obtener la página: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }
    curl_close($ch);

    // Procesar el HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Función para extraer datos
    function extractData($xpath, $query, $isCedula = false) {
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            $text = trim($nodes[0]->nodeValue);
            if ($isCedula && preg_match('/\d{10}/', $text, $matches)) {
                return $matches[0];
            }
            return $text;
        }
        return null;
    }

    // Extraer datos
    $datos = [
        'cedula' => extractData($xpath, "//label[contains(@id, 'j_idt31')]", true),
        'nombres' => extractData($xpath, "//label[contains(@id, 'j_idt19')]"),
        'apellidos' => extractData($xpath, "//label[contains(@id, 'j_idt21')]"),
        'fecha_nacimiento' => formatDate(extractData($xpath, "//label[contains(@id, 'j_idt23')]")),
        'condicion_cedula' => extractData($xpath, "//label[contains(@id, 'j_idt25')]"),
        'lugar_expedicion' => extractData($xpath, "//label[contains(@id, 'j_idt27')]"),
        'fecha_expedicion' => formatDate(extractData($xpath, "//label[contains(@id, 'j_idt29')]"))
    ];

    // Función para formatear fechas
    function formatDate($dateStr) {
        if (empty($dateStr)) return null;
        $parts = explode('-', $dateStr);
        return count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $dateStr;
    }

    echo json_encode(array_filter($datos));
    exit;
}

echo json_encode(['error' => 'Acceso no autorizado - Parámetros incorrectos']);
?>