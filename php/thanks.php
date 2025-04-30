<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}
$avg = isset($_GET['avg']) ? number_format((float)$_GET['avg'], 2) : '0.00';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>¡Gracias!</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 90%;
      padding: 30px;
      text-align: center;
    }
    .card h1 {
      color: #003366;
      margin-bottom: 20px;
      font-size: 1.8em;
    }
    .card p {
      font-size: 1.1em;
      margin: 10px 0 20px;
      color: #444;
    }
    .card .avg {
      display: inline-block;
      background: #e9f7ef;
      color: #155724;
      border: 1px solid #c3e6cb;
      padding: 10px 20px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 1.4em;
    }
    .card a {
      display: inline-block;
      margin-top: 25px;
      padding: 12px 25px;
      background: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s;
    }
    .card a:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>¡Gracias por tu evaluación!</h1>
    <p>Tu promedio fue:</p>
    <div class="avg"><?= $avg ?></div>
    <p><a href="dashboard_estudiante.php">← Volver al inicio</a></p>
  </div>
</body>
</html>
