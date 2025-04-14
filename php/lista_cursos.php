<?php
session_start();
require('config.php');

// Verificar que el usuario es admin; si es necesario:
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

// Consulta para traer todos los cursos
$query = "SELECT id, nombre, descripcion FROM courses ORDER BY fecha_creacion DESC";
$result = $mysqli->query($query);

if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Cursos - Informe</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #003366;
            color: #fff;
        }
        .action-btn {
            padding: 6px 12px;
            background: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .action-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Listado de Cursos</h1>
    <table>
        <thead>
            <tr>
                <th>ID Curso</th>
                <th>Nombre del Curso</th>
                <th>Descripción</th>
                <th>Informe PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['id']); ?></td>
                    <td><?php echo htmlspecialchars($course['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($course['descripcion']); ?></td>
                    <!-- El enlace incluye el course_id y dirige al informe -->
                    <td><a class="action-btn" href="informe_curso.php?course_id=<?php echo $course['id']; ?>">Generar Informe</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
