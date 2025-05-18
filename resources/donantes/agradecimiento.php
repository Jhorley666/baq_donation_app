<?php
require_once 'includes/db.php';

if (!isset($_GET['donante_id'])) {
    header('Location: registro.php');
    exit();
}

$donante_id = intval($_GET['donante_id']);
$metodo = $_GET['metodo'] ?? 'general';

try {
    // Consulta modificada para incluir metodo_autorizacion y fecha_autorizacion
    $stmt = $pdo->prepare("SELECT nombres, apellidos, correo_electronico, seguimiento, metodo_autorizacion, fecha_autorizacion FROM donantes WHERE id = ?");
    $stmt->execute([$donante_id]);
    $donante = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $donante = [
        'nombres' => '', 
        'apellidos' => '', 
        'correo_electronico' => '', 
        'seguimiento' => '',
        'metodo_autorizacion' => '',
        'fecha_autorizacion' => ''
    ];
}

$pdf_url = !empty($donante['seguimiento']) ? 'https://'.$_SERVER['HTTP_HOST'].'/'.$donante['seguimiento'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por su donación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .thank-you-container {
            text-align: center;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .thank-you-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .pdf-container {
            margin: 30px 0;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
        }
        .pdf-viewer {
            width: 100%;
            height: 500px;
            border: none;
            border-radius: 5px;
        }
        .share-buttons { 
            margin-top: 20px;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .share-btn { 
            margin: 0 5px;
            min-width: 120px;
        }
        .slogan-carousel {
            font-size: 1.5rem;
            font-weight: 300;
            min-height: 100px;
            margin: 2rem 0;
            color: #2c3e50;
        }
        .slogan {
            display: none;
            animation: fadeIn 1s;
        }
        .slogan.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .method-badge {
            font-size: 1rem;
            padding: 8px 15px;
            border-radius: 20px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .btn-whatsapp {
            background-color: #25D366;
            border-color: #25D366;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="thank-you-container">
            <div class="thank-you-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <h1>¡Autorización Completada!</h1>
            <p class="lead">Gracias <?= htmlspecialchars($donante['nombres']) ?> por su apoyo.</p>
            
            <!-- Badge según método de autorización -->
            <?php if (!empty($donante['metodo_autorizacion'])): ?>
                <div class="method-badge 
                    <?php 
                    switch($donante['metodo_autorizacion']) {
                        case 'p12': echo 'bg-info text-white'; break;
                        case 'fotos': echo 'bg-warning text-dark'; break;
                        case 'manual': echo 'bg-secondary text-white'; break;
                    }
                    ?>">
                    <i class="bi 
                        <?php 
                        switch($donante['metodo_autorizacion']) {
                            case 'p12': echo 'bi-file-lock'; break;
                            case 'fotos': echo 'bi-camera'; break;
                            case 'manual': echo 'bi-pen'; break;
                        }
                        ?>">
                    </i> 
                    <?php 
                    switch($donante['metodo_autorizacion']) {
                        case 'p12': echo 'Firmado electrónicamente con certificado P12'; break;
                        case 'fotos': echo 'Autorizado con evidencia fotográfica'; break;
                        case 'manual': echo 'Firmado manualmente'; break;
                    }
                    ?>
                </div>
                
                <p class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Autorizado el <?= !empty($donante['fecha_autorizacion']) ? date('d/m/Y H:i', strtotime($donante['fecha_autorizacion'])) : date('d/m/Y H:i') ?>
                </p>
            <?php endif; ?>
            
           <!-- Slider de mensajes de agradecimiento -->
           <div class="slogan-carousel">
                <div class="slogan active">
                    <i class="bi bi-heart-fill text-danger"></i> Hoy apoyas al Banco de Alimentos, mañana podrías necesitarlo
                </div>
                <div class="slogan">
                    <i class="bi bi-share-fill text-primary"></i> Comparte tu apoyo e inspira a otros a donar
                </div>
                <div class="slogan">
                    <i class="bi bi-people-fill text-success"></i> Juntos podemos combatir el hambre en nuestra comunidad
                </div>
                <div class="slogan">
                    <i class="bi bi-lightbulb-fill text-warning"></i> Tu donación hace posible que nadie se vaya a dormir con hambre
                </div>
            </div>
            
            <!-- Visualización del PDF -->
            <?php if (!empty($donante['seguimiento']) && file_exists(__DIR__.'/'.$donante['seguimiento'])): ?>
            <div class="pdf-container">
                <h4>Tu autorización de donación:</h4>
                <iframe src="<?= htmlspecialchars($donante['seguimiento']) ?>" class="pdf-viewer"></iframe>
                <div class="mt-3">
                    <a href="<?= htmlspecialchars($donante['seguimiento']) ?>" target="_blank" class="btn btn-primary">
                        <i class="bi bi-download"></i> Descargar PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Botones para compartir -->
            <div class="share-buttons">
                <h5>¡Comparte tu apoyo en redes sociales!</h5>
                <p>Ayúdanos a inspirar a más personas a donar</p>
                
                <?php if (!empty($pdf_url)): ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($pdf_url) ?>" 
                   target="_blank" class="btn btn-primary share-btn">
                    <i class="bi bi-facebook"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode('He apoyado al @BancodeAlimentosQuito con mi donación. ¡Únete tú también!') ?>&url=<?= urlencode($pdf_url) ?>" 
                   target="_blank" class="btn btn-info share-btn">
                    <i class="bi bi-twitter"></i> Twitter
                </a>
                <a href="mailto:?subject=He donado al Banco de Alimentos&body=Me enorgullece compartir que he realizado mi donación al Banco de Alimentos. <?= urlencode($pdf_url) ?>" 
                   class="btn btn-success share-btn">
                    <i class="bi bi-envelope"></i> Email
                </a>
                <a href="https://wa.me/?text=<?= urlencode('¡He realizado mi donación al Banco de Alimentos! Hoy ayudamos nosotros, mañana podríamos necesitar ayuda. ' . $pdf_url) ?>" 
                   target="_blank" class="btn btn-success share-btn btn-whatsapp">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
                <?php endif; ?>
            </div>
            
            <div class="mt-4">
                <a href="https://www.baq.ec/" class="btn btn-outline-primary">
                    <i class="bi bi-house"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Carrusel de slogans
        const slogans = document.querySelectorAll('.slogan');
        if (slogans.length > 0) {
            let current = 0;
            
            function rotateSlogans() {
                slogans[current].classList.remove('active');
                current = (current + 1) % slogans.length;
                slogans[current].classList.add('active');
            }
            
            // Rotar cada 3 segundos
            setInterval(rotateSlogans, 3000);
        }
    </script>
</body>
</html>