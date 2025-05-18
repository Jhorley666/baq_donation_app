<?php
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: registro.php?error=ID no proporcionado');
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
    
    // Crear directorio personalizado si no existe
    $dir_base = 'autorizaciones/'.slugify($donante['nombres'].'_'.$donante['cedula']);
    if (!file_exists($dir_base)) {
        mkdir($dir_base, 0755, true);
    }
    
    $pdf_url = $dir_base.'/autorizacion_'.$donante['cedula'].'.pdf';
    
} catch (PDOException $e) {
    header('Location: registro.php?error='.urlencode($e->getMessage()));
    exit();
}

// Función para crear slugs seguros para nombres de directorio
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '_', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '_');
    $text = strtolower($text);
    return $text;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorización de Débito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .preview-image {
            max-width: 300px;
            max-height: 200px;
            margin-top: 10px;
        }
        .hero-section {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .slogan-carousel {
            font-size: 1.8rem;
            font-weight: 300;
            min-height: 80px;
            text-align: center;
            margin: 1rem 0;
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
        .policy-note {
            background-color: #FFF3E0;
            border-left: 5px solid #FF9800;
            padding: 1rem;
            margin: 2rem 0;
            font-size: 0.9rem;
        }
        .footer-policy {
            background-color: #FF9800;
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
            border-radius: 20px 20px 0 0;
        }
        .method-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: none;
        }
        .method-header {
            padding: 1.5rem;
            font-weight: 600;
        }
        .nav-tabs .nav-link {
            font-weight: 500;
        }
        .btn-donate {
            background-color: #FF5722;
            border-color: #FF5722;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section con mensajes rotativos -->
    <div class="hero-section">
        <div class="container text-center">
            <h1><i class="bi bi-heart-fill"></i> Gracias por apoyar al Banco de Alimentos de Quito</h1>
            <div class="slogan-carousel">
                <div class="slogan active">"Tu donación alimenta esperanzas"</div>
                <div class="slogan">"Juntos contra el hambre"</div>
                <div class="slogan">"Cada aporte hace la diferencia"</div>
                <div class="slogan">"Alimentando corazones, transformando vidas"</div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Nota sobre cancelación -->
        <div class="policy-note">
            <p><i class="bi bi-info-circle-fill"></i> <strong>Importante:</strong> Puedes suspender tu autorización de débito en cualquier momento comunicándote con nosotros. Tu ayuda es voluntaria y agradecemos tu apoyo.</p>
        </div>

        <!-- Tarjeta de información del donante -->
        <div class="card method-card mb-4">
            <div class="card-header bg-primary text-white method-header">
                <h2><i class="bi bi-person-badge"></i> Tus Datos de Donación</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-person"></i> Nombre:</strong> <?= isset($donante) ? htmlspecialchars($donante['nombres'].' '.$donante['apellidos']) : '' ?></p>
                        <p><strong><i class="bi bi-credit-card"></i> Cédula:</strong> <?= isset($donante) ? htmlspecialchars($donante['cedula']) : '' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-bank"></i> Banco:</strong> <?= isset($donante) ? htmlspecialchars($donante['banco']) : '' ?></p>
                        <p><strong><i class="bi bi-cash-stack"></i> Monto:</strong> $<?= isset($donante) ? number_format($donante['monto_donar'], 2) : '0.00' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resto del código HTML permanece igual... -->
                 <!-- Métodos de autorización -->
        <h3 class="mb-4 text-center"><i class="bi bi-pen-fill"></i> Elige tu método de autorización:</h3>

<!-- Pestañas para diferentes métodos -->
<ul class="nav nav-tabs" id="authTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="p12-tab" data-bs-toggle="tab" data-bs-target="#p12" type="button">
            <i class="bi bi-file-lock"></i> Firma Electrónica (P12)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="foto-tab" data-bs-toggle="tab" data-bs-target="#foto" type="button">
            <i class="bi bi-camera"></i> Autorización con Fotos
        </button>
    </li>
</ul>

<div class="tab-content p-4 border border-top-0 rounded-bottom" id="authTabsContent">
    <!-- Pestaña de Firma P12 -->
    <div class="tab-pane fade show active" id="p12" role="tabpanel">
        <form id="p12Form" action="firmar_p12.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="donante_id" value="<?= $donante_id ?>">
            
            <div class="mb-3">
                <label for="certificado" class="form-label">Certificado .p12</label>
                <input class="form-control" type="file" id="certificado" name="certificado" accept=".p12,.pfx" required>
                <small class="text-muted">Seleccione su certificado digital en formato .p12</small>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña del certificado</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3">
                <label for="razon" class="form-label">Razón de la firma</label>
                <input type="text" class="form-control" id="razon" name="razon" value="Autorización de débito bancario" required>
            </div>
            
            <div class="mb-3">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="Quito, Ecuador" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-donate">
                <i class="bi bi-file-lock"></i> Firmar y Autorizar
            </button>
        </form>
    </div>

    <!-- Pestaña de Autorización con Fotos -->
    <div class="tab-pane fade" id="foto" role="tabpanel">
        <form id="fotoForm" action="autorizar_con_fotos.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="donante_id" value="<?= $donante_id ?>">
            
            <div class="mb-3">
                <label for="foto_cedula" class="form-label">Foto de la Cédula</label>
                <input class="form-control" type="file" id="foto_cedula" name="foto_cedula" accept="image/*" required>
                <small class="text-muted">Suba una foto legible de su cédula</small>
                <img id="previewCedula" class="preview-image d-none">
            </div>
            
            <div class="mb-3">
                <label for="foto_persona" class="form-label">Foto sujetando la Cédula</label>
                <input class="form-control" type="file" id="foto_persona" name="foto_persona" accept="image/*" required>
                <small class="text-muted">Suba una foto donde se le vea claramente sosteniendo su cédula</small>
                <img id="previewPersona" class="preview-image d-none">
            </div>
            
            <button type="submit" class="btn btn-primary btn-donate">
                <i class="bi bi-camera"></i> Subir Fotos y Autorizar
            </button>
        </form>
    </div>
</div>

<!-- Opción de Firma Manual -->
<div class="card method-card mt-4">
    <div class="card-header bg-info text-white method-header">
        <h3><i class="bi bi-pen"></i> Opción 3: Firmar Manualmente</h3>
    </div>
    <div class="card-body">
        <form action="procesar_firma_manual.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="donante_id" value="<?= $donante_id ?>">
            
            <div class="mb-3">
                <label class="form-label">Descargar documento para firma manual:</label>
                <a href="generar_pdf.php?id=<?= $donante_id ?>" class="btn btn-outline-primary mb-3">
                    <i class="bi bi-download"></i> Descargar PDF para Firma Manual
                </a>
            </div>
            
            <div class="mb-3">
                <label for="documento_firmado" class="form-label">Subir documento firmado:</label>
                <input type="file" class="form-control" id="documento_firmado" name="documento_firmado" accept=".pdf,.jpg,.png" required>
                <small class="text-muted">Suba el PDF o imagen del documento firmado manualmente</small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-donate">
                <i class="bi bi-cloud-upload"></i> Subir Documento Firmado
            </button>
        </form>
    </div>
</div>
</div>
        <!-- ... (mantener todo el resto del código HTML igual que en tu versión original) ... -->

    </div>

    <!-- Sección de políticas en naranja -->
    <div class="footer-policy">
        <div class="container">
            <h4><i class="bi bi-shield-check"></i> Políticas y Términos</h4>
            <p>Al autorizar el débito automático, aceptas que el Banco de Alimentos pueda realizar cargos periódicos a tu cuenta según el monto indicado. Puedes cancelar esta autorización en cualquier momento.</p>
            <p>Nos comprometemos a usar tus datos solo para fines de procesamiento de donaciones y nunca los compartiremos con terceros sin tu consentimiento.</p>
            <p class="mt-3"><small>© <?= date('Y') ?> Banco de Alimentos. Todos los derechos reservados.</small></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview de imágenes
        document.getElementById('foto_cedula')?.addEventListener('change', function(e) {
            const preview = document.getElementById('previewCedula');
            const file = e.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        });
        
        document.getElementById('foto_persona')?.addEventListener('change', function(e) {
            const preview = document.getElementById('previewPersona');
            const file = e.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        });

        // Carrusel de slogans
        const slogans = document.querySelectorAll('.slogan');
        if (slogans.length > 0) {
            let current = 0;
            
            function rotateSlogans() {
                slogans[current].classList.remove('active');
                current = (current + 1) % slogans.length;
                slogans[current].classList.add('active');
            }
            
            setInterval(rotateSlogans, 3000);
        }
    </script>
</body>
</html>