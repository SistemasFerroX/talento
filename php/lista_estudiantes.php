<?php
session_start();
require('config.php');

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.html");
    exit;
}

// Consulta para traer todos los estudiantes y sus empresas
$query = "SELECT id, nombre_completo, empresa FROM users WHERE rol = 'estudiante' ORDER BY nombre_completo ASC";
$result = $mysqli->query($query);
if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Estudiantes</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background: #003366;
            color: #fff;
        }
        .action-btn {
            padding: 6px 10px;
            background: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .action-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Lista de Estudiantes</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Empresa</th>
                <th>Informe PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                    <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                    <td>
                        <a class="action-btn" href="informe_estudiante_admin.php?student_id=<?php echo $row['id']; ?>">
                            Generar Informe
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
