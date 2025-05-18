<?php 
require_once 'includes/db.php';

// Mostrar mensajes de éxito/error si existen
$mensaje = '';
$tipo_mensaje = '';
$mostrar_descarga = false;
$pdf_url = '';

if (isset($_GET['exito']) && $_GET['exito'] == '1') {
    $mensaje = 'Donante registrado correctamente!';
    $tipo_mensaje = 'success';
    
    if (isset($_GET['donante_id'])) {
        $mostrar_descarga = true;
        $pdf_url = 'autorizaciones/autorizacion_'.$_GET['donante_id'].'.pdf';
    }
} elseif (isset($_GET['error'])) {
    $mensaje = 'Error al registrar donante: ' . htmlspecialchars($_GET['error']);
    $tipo_mensaje = 'danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Donantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #FF6347;
            color: white;
        }
        .container-form {
            background-color: white;
            color: #333;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .title {
            color: white;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            animation: fadeIn 0.5s, fadeOut 0.5s 4.5s;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        @keyframes fadeOut {
            from {opacity: 1; transform: translateY(0);}
            to {opacity: 0; transform: translateY(-20px);}
        }
    </style>
</head>
<body>
    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="container py-4">
        <h1 class="text-center title mb-4">Registro de Donantes</h1>
        
        <div class="container-form">
            <form id="formDonante" action="procesar.php" method="post">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cedula" class="form-label">Cédula*</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="btnEscanearQR">
                            <i class="bi bi-qr-code-scan"></i> Escanear QR
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombres" class="form-label">Nombres*</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" required>
                    </div>
                    <div class="col-md-6">
                        <label for="apellidos" class="form-label">Apellidos*</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento*</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="col-md-6">
                        <label for="condicion_cedula" class="form-label">Condición de Cédula</label>
                        <input type="text" class="form-control" id="condicion_cedula" name="condicion_cedula">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lugar_expedicion" class="form-label">Lugar de Expedición</label>
                        <input type="text" class="form-control" id="lugar_expedicion" name="lugar_expedicion">
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_expedicion" class="form-label">Fecha de Expedición</label>
                        <input type="date" class="form-control" id="fecha_expedicion" name="fecha_expedicion">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="col-md-6">
                        <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo_electronico" name="correo_electronico">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="banco" class="form-label">Banco</label>
                        <select class="form-select" id="banco" name="banco">
                            <option value="">Seleccione...</option>
                            <option value="Pichincha">Pichincha</option>
                            <option value="Guayaquil">Guayaquil</option>
                            <option value="Pacífico">Pacífico</option>
                            <option value="Bolivariano">Bolivariano</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="tipo_cuenta" class="form-label">Tipo de Cuenta</label>
                        <select class="form-select" id="tipo_cuenta" name="tipo_cuenta">
                            <option value="">Seleccione...</option>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="numero_cuenta" class="form-label">Número de Cuenta</label>
                        <input type="text" class="form-control" id="numero_cuenta" name="numero_cuenta">
                    </div>
                    <div class="col-md-6">
                        <label for="monto_donar" class="form-label">Monto a Donar</label>
                        <input type="number" step="0.01" class="form-control" id="monto_donar" name="monto_donar">
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Registrar Donante
                    </button>
                </div>
            </form>

            <?php if ($mostrar_descarga): ?>
            <div class="text-center mt-4">
                <a href="<?= $pdf_url ?>" class="btn btn-success btn-lg" download>
                    <i class="bi bi-download"></i> Descargar Autorización de Débito
                </a>
                <button onclick="imprimirPDF('<?= $pdf_url ?>')" class="btn btn-primary btn-lg ms-2">
                    <i class="bi bi-printer"></i> Imprimir Autorización
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar alerta automáticamente
            const alerta = document.querySelector('.alert');
            if (alerta) {
                setTimeout(() => {
                    alerta.style.display = 'none';
                }, 5000);
            }

            // Autollenado desde QR
            const qrData = sessionStorage.getItem('qrData');
            
            if (qrData && new URLSearchParams(window.location.search).has('qr')) {
                const data = JSON.parse(qrData);
                
                function formatDateForInput(dateStr) {
                    if (!dateStr) return '';
                    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
                    const parts = dateStr.split('-');
                    return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
                }
                
                if (data.cedula) document.getElementById('cedula').value = data.cedula;
                if (data.nombres) document.getElementById('nombres').value = data.nombres;
                if (data.apellidos) document.getElementById('apellidos').value = data.apellidos;
                if (data.fecha_nacimiento) {
                    document.getElementById('fecha_nacimiento').value = formatDateForInput(data.fecha_nacimiento);
                }
                if (data.condicion_cedula) {
                    document.getElementById('condicion_cedula').value = data.condicion_cedula;
                }
                if (data.lugar_expedicion) {
                    document.getElementById('lugar_expedicion').value = data.lugar_expedicion;
                }
                if (data.fecha_expedicion) {
                    document.getElementById('fecha_expedicion').value = formatDateForInput(data.fecha_expedicion);
                }
                
                sessionStorage.removeItem('qrData');
                
                // Mostrar notificación
                const alerta = document.createElement('div');
                alerta.className = 'alert alert-success position-fixed top-0 end-0 m-3';
                alerta.innerHTML = 'Datos del QR cargados correctamente!';
                document.body.appendChild(alerta);
                setTimeout(() => alerta.remove(), 3000);
            }
            
            // Botón para abrir el escáner
            document.getElementById('btnEscanearQR').addEventListener('click', function() {
                window.location.href = 'scanner.php';
            });
        });

        function imprimirPDF(url) {
            window.open(url, '_blank').print();
        }
    </script>
</body>
</html>