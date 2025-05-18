<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear QR - Registro Civil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .scanner-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        #scanner {
            width: 100%;
            margin: 20px 0;
            border: 3px solid #28a745;
            border-radius: 8px;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="scanner-container text-center">
            <h2 class="mb-4">Escanear Código QR</h2>
            <p class="lead">Apunta el código QR de la cédula hacia la cámara</p>
            
            <div class="mb-3">
                <select id="camara" class="form-select">
                    <option value="">Seleccionar cámara...</option>
                </select>
            </div>
            
            <canvas id="scanner" hidden></canvas>
            <video id="video" playsinline autoplay class="w-100"></video>
            
            <div id="result" class="text-start" hidden>
                <h4>Datos detectados:</h4>
                <pre id="datos"></pre>
            </div>
            
            <div class="mt-4">
                <button id="btnUsarDatos" class="btn btn-success btn-lg" disabled>
                    Usar estos datos
                </button>
                <a href="registro.php" class="btn btn-secondary btn-lg ms-2">
                    Cancelar
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('scanner');
            const ctx = canvas.getContext('2d');
            const datosElement = document.getElementById('datos');
            const resultDiv = document.getElementById('result');
            const btnUsarDatos = document.getElementById('btnUsarDatos');
            const selectCamara = document.getElementById('camara');
            
            let scannerActivo = false;
            let datosQR = null;
            let stream = null;

            // Cargar dispositivos de cámara disponibles
            async function cargarCamaras() {
                try {
                    const dispositivos = await navigator.mediaDevices.enumerateDevices();
                    const camaras = dispositivos.filter(dispositivo => dispositivo.kind === 'videoinput');
                    
                    camaras.forEach((camara, i) => {
                        const option = document.createElement('option');
                        option.value = camara.deviceId;
                        option.text = camara.label || `Cámara ${i + 1}`;
                        selectCamara.appendChild(option);
                    });
                    
                    if (camaras.length > 0) {
                        iniciarEscaneo(camaras[0].deviceId);
                    }
                } catch (error) {
                    console.error('Error al cargar cámaras:', error);
                }
            }

            // Iniciar el escaneo con la cámara seleccionada
            async function iniciarEscaneo(deviceId) {
                detenerEscaneo();
                
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            deviceId: deviceId ? { exact: deviceId } : undefined,
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });
                    
                    video.srcObject = stream;
                    video.play();
                    scannerActivo = true;
                    
                    // Ajustar canvas al tamaño del video
                    video.addEventListener('play', () => {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        escanearFrame();
                    });
                } catch (error) {
                    console.error('Error al acceder a la cámara:', error);
                    alert('No se pudo acceder a la cámara. Asegúrate de permitir el acceso.');
                }
            }

            // Detener el escaneo
            function detenerEscaneo() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    scannerActivo = false;
                }
            }

            // Escanear cada frame
            function escanearFrame() {
                if (!scannerActivo) return;
                
                try {
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert',
                    });
                    
                    if (code) {
                        try {
                            datosQR = procesarDatosQR(code.data);
                            mostrarDatos(datosQR);
                            detenerEscaneo();
                        } catch (error) {
                            console.error('Error al procesar QR:', error);
                        }
                    } else {
                        requestAnimationFrame(escanearFrame);
                    }
                } catch (error) {
                    console.error('Error en escaneo:', error);
                    requestAnimationFrame(escanearFrame);
                }
            }

            // Procesar los datos del QR (similar a tu función en Python)
            function procesarDatosQR(qrData) {
                // Parsear la URL del QR
                const url = new URL(qrData);
                const params = new URLSearchParams(url.search);
                
                // Extraer datos específicos (ajusta según el formato real del QR)
                return {
                    cedula: params.get('cedula') || '',
                    nombres: params.get('nombres') || '',
                    apellidos: params.get('apellidos') || '',
                    fecha_nacimiento: params.get('fechaNacimiento') || '',
                    condicion_cedula: params.get('condicion') || '',
                    lugar_expedicion: params.get('lugarExpedicion') || '',
                    fecha_expedicion: params.get('fechaExpedicion') || ''
                };
            }

            // Mostrar los datos detectados
            function mostrarDatos(datos) {
                video.hidden = true;
                canvas.hidden = false;
                resultDiv.hidden = false;
                btnUsarDatos.disabled = false;
                
                datosElement.textContent = JSON.stringify(datos, null, 2);
            }

            // Event listeners
            selectCamara.addEventListener('change', () => {
                iniciarEscaneo(selectCamara.value);
            });
            
            btnUsarDatos.addEventListener('click', () => {
                if (datosQR) {
                    // Guardar datos en sessionStorage para usarlos en el formulario
                    sessionStorage.setItem('qrData', JSON.stringify(datosQR));
                    window.location.href = 'registro.php?qr=1';
                }
            });
            
            // Iniciar
            cargarCamaras();
            
            // Limpiar al salir
            window.addEventListener('beforeunload', detenerEscaneo);
        });
    </script>
</body>
</html>