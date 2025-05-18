<?php
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escáner QR - Registro Civil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #qr-video {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            display: block;
        }
        #qr-result {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="mb-4">Escáner QR de Cédula</h1>
                
                <div id="camera-access" class="alert alert-info">
                    <h4>Permiso para usar la cámara</h4>
                    <p>Necesitamos acceso a tu cámara para escanear el código QR.</p>
                    <button id="start-camera" class="btn btn-primary">Activar Cámara</button>
                </div>

                <div id="scanner-container" style="display: none;">
                    <video id="qr-video"></video>
                    <div id="qr-result"></div>
                    <p class="mt-2">Apunta al código QR de la cédula</p>
                </div>

                <div id="manual-input" class="mt-4" style="display: none;">
                    <h5>O ingresa manualmente el enlace del QR</h5>
                    <input type="text" id="qr-url" class="form-control" placeholder="https://qr.registrocivil.gob.ec/...">
                    <button id="process-url" class="btn btn-secondary mt-2">Procesar</button>
                </div>

                <button id="btn-volver" class="btn btn-secondary mt-4" style="display: none;">
                    Volver al Formulario
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        const video = document.getElementById('qr-video');
        const resultContainer = document.getElementById('qr-result');
        const startCameraBtn = document.getElementById('start-camera');
        const scannerContainer = document.getElementById('scanner-container');
        const cameraAccessDiv = document.getElementById('camera-access');
        const manualInputDiv = document.getElementById('manual-input');
        const qrUrlInput = document.getElementById('qr-url');
        const processUrlBtn = document.getElementById('process-url');
        const btnVolver = document.getElementById('btn-volver');

        let stream = null;
        let scanning = false;

        // 1. Manejar el botón para activar cámara
        startCameraBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "environment",
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });
                
                video.srcObject = stream;
                video.play();
                
                cameraAccessDiv.style.display = 'none';
                scannerContainer.style.display = 'block';
                manualInputDiv.style.display = 'block';
                btnVolver.style.display = 'block';
                
                startScanning();
            } catch (err) {
                console.error("Error al acceder a la cámara:", err);
                resultContainer.style.display = 'block';
                resultContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Error al acceder a la cámara: ${err.message || 'Permiso denegado'}
                    </div>
                    <p>Puedes ingresar manualmente el enlace del QR</p>
                `;
                manualInputDiv.style.display = 'block';
            }
        });

        // 2. Función para escanear continuamente
        function startScanning() {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            scanning = true;

            function scan() {
                if (!scanning) return;
                
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert"
                    });
                    
                    if (code) {
                        stopScanning();
                        processQRCode(code.data);
                    }
                }
                
                requestAnimationFrame(scan);
            }
            
            scan();
        }

        // 3. Detener el escaneo
        function stopScanning() {
            scanning = false;
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        }

        // 4. Procesar el código QR encontrado
        function processQRCode(qrData) {
            resultContainer.style.display = 'block';
            
            // Verificar si es un enlace del Registro Civil
            if (qrData.includes('registrocivil.gob.ec/qr')) {
                resultContainer.innerHTML = `
                    <div class="alert alert-info">
                        Enlace detectado: ${qrData}<br>
                        Extrayendo datos...
                    </div>
                `;
                
                // Enviar el enlace al servidor para extraer los datos
                fetch('extract_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ url: qrData })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resultContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Error: ${data.error}
                            </div>
                            <button onclick="window.location.href='registro.php?manual=1'" class="btn btn-warning">
                                Ingresar datos manualmente
                            </button>
                        `;
                    } else {
                        // Almacenar datos para el formulario
                        sessionStorage.setItem('qrData', JSON.stringify(data));
                        resultContainer.innerHTML = `
                            <div class="alert alert-success">
                                Datos extraídos correctamente! Redirigiendo...
                            </div>
                        `;
                        setTimeout(() => {
                            window.location.href = 'registro.php?qr=1';
                        }, 2000);
                    }
                })
                .catch(error => {
                    resultContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Error al procesar: ${error.message}
                        </div>
                    `;
                });
            } else {
                resultContainer.innerHTML = `
                    <div class="alert alert-warning">
                        El código QR no es del Registro Civil. Por favor escanee el QR de la cédula.
                    </div>
                `;
                setTimeout(() => {
                    startScanning();
                }, 3000);
            }
        }

        // 5. Opción manual para ingresar URL
        processUrlBtn.addEventListener('click', () => {
            const url = qrUrlInput.value.trim();
            if (url && url.includes('registrocivil.gob.ec/qr')) {
                processQRCode(url);
            } else {
                alert('Por favor ingrese un enlace válido del Registro Civil');
            }
        });

        // 6. Botón para volver
        btnVolver.addEventListener('click', () => {
            stopScanning();
            window.location.href = 'registro.php';
        });
    </script>
</body>
</html>