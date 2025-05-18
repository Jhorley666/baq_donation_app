<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$url = filter_var($data['url'] ?? '', FILTER_VALIDATE_URL);

if (!$url || !str_contains($url, 'registrocivil.gob.ec')) {
    echo json_encode(['error' => 'URL no válida']);
    exit;
}

// Usar cURL para obtener el contenido de la página
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para desarrollo, quitar en producción
$html = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Error al obtener la página: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Parsear el HTML para extraer los datos
$dom = new DOMDocument();
libxml_use_internal_errors(true); // Suprimir errores de HTML mal formado
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Función auxiliar para extraer datos
function extractData($xpath, $query) {
    $nodes = $xpath->query($query);
    return $nodes->length > 0 ? trim($nodes[0]->nodeValue) : null;
}

// Extraer los datos específicos (ajusta estos selectores según la página actual)
$datos = [
    'cedula' => extractData($xpath, "//label[contains(@id, 'j_idt31')]"),
    'nombres' => extractData($xpath, "//label[contains(@id, 'j_idt19')]"),
    'apellidos' => extractData($xpath, "//label[contains(@id, 'j_idt21')]"),
    'fecha_nacimiento' => formatDate(extractData($xpath, "//label[contains(@id, 'j_idt23')]")),
    'condicion_cedula' => extractData($xpath, "//label[contains(@id, 'j_idt25')]"),
    'lugar_expedicion' => extractData($xpath, "//label[contains(@id, 'j_idt27')]"),
    'fecha_expedicion' => formatDate(extractData($xpath, "//label[contains(@id, 'j_idt29')]"))
];

// Función para formatear fechas de "dd-mm-aaaa" a "aaaa-mm-dd"
function formatDate($dateStr) {
    if (empty($dateStr)) return null;
    
    $parts = explode('-', $dateStr);
    if (count($parts) === 3) {
        return sprintf('%s-%s-%s', $parts[2], $parts[1], $parts[0]);
    }
    
    return $dateStr;
}

// Filtrar valores nulos y devolver resultado
echo json_encode(array_filter($datos));

function extractData($xpath, $query) {
    $nodes = $xpath->query($query);
    if ($nodes->length > 0) {
        // Extraer número de cédula del texto "El documento X se encuentra vigente"
        if (strpos($query, 'j_idt31') !== false) {
            $text = $nodes[0]->nodeValue;
            if (preg_match('/documento (\d+)/', $text, $matches)) {
                return $matches[1];
            }
        }
        return trim($nodes[0]->nodeValue);
    }
    return null;
}
?>