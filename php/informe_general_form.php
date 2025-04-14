<?php
session_start();
// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Informe General - Seleccionar Fechas</title>
  <style>
    /* Fondo con gradiente */
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #6ec1e4, #e2e2e2);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Contenedor principal */
    .container {
      background: #ffffff;
      width: 400px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 30px;
      text-align: center;
    }

    .container h2 {
      margin: 0 0 15px;
      color: #003366;
      font-size: 22px;
    }

    /* Estilo de las etiquetas y campos */
    label {
      display: block;
      text-align: left;
      margin: 15px 0 5px;
      font-weight: bold;
      color: #444;
    }

    input[type="date"] {
      width: 100%;
      padding: 10px;
      margin: 0 auto;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }

    /* Botones */
    .btn-group {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .btn {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
      color: #fff;
      text-decoration: none;
      text-align: center;
      transition: background 0.3s;
    }

    .btn-generate {
      background: #007bff;
    }
    .btn-generate:hover {
      background: #0056b3;
    }

    .btn-dashboard {
      background: #28a745;
    }
    .btn-dashboard:hover {
      background: #1e7e34;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Informe General</h2>
    <form action="informe_general.php" method="GET">
      <label for="fecha_inicio">Fecha de Inicio:</label>
      <input type="date" name="fecha_inicio" id="fecha_inicio" required>
      
      <label for="fecha_fin">Fecha de Fin:</label>
      <input type="date" name="fecha_fin" id="fecha_fin" required>

      <div class="btn-group">
        <!-- Botón para generar el informe -->
        <button type="submit" class="btn btn-generate">
          Generar Informe
        </button>

        <!-- Botón para volver al dashboard -->
        <a href="dashboard_admin.php" class="btn btn-dashboard">
          Volver al Dashboard
        </a>
      </div>
    </form>
  </div>
</body>
</html>
