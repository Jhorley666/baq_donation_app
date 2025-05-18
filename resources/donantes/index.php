<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco de Alimentos Quito</title>
    <style>
        body {
            background-color: #FFA500; /* Color naranja */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        
        .container {
            max-width: 800px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        p {
            color: #555;
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .btn {
            background-color: #FF6B00;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #E05D00;
        }
        
        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 15px;
            }
            
            .logo {
                max-width: 150px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="logo1.png" alt="Logo Banco de Alimentos Quito" class="logo">
        
        <h1>¡Gracias por registrar su autorización de débito bancario!</h1>
        
        <p>Su apoyo es fundamental para continuar con nuestra labor en el Banco de Alimentos de Quito.</p>
        
        <a href="registro.php" class="btn">Ir al Registro</a>
    </div>
</body>
</html>