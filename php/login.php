<?php
ob_start();
ini_set('display_errors', 0); // En producción, no mostramos errores
ini_set('log_errors', 1);     // Pero sí los registramos
error_reporting(E_ALL);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',       // O 'localhost' si lo prefieres
    'secure'   => false,    // false, porque usas HTTP, no HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Escapar la cédula para prevenir inyección SQL
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    $password = $_POST['password'];
    
    // Buscar el usuario por cédula
    $query = "SELECT id, nombre_completo, password, rol FROM users WHERE cedula = '$cedula' LIMIT 1";
    $result = $mysqli->query($query);
    
    if (!$result) {
        error_log("Error en la consulta: " . $mysqli->error);
        die("Error interno. Por favor, inténtelo más tarde.");
    }
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre_completo'];
            $_SESSION['rol'] = $user['rol'];
            
            // Redirigir según el rol del usuario
            if ($user['rol'] == 'admin') {
                header("Location: dashboard_admin.php");
            } elseif ($user['rol'] == 'profesor') {
                header("Location: dashboard_profesor.php");
            } else {
                header("Location: dashboard_estudiante.php");
            }
            exit;
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado para cédula: $cedula";
    }
} else {
    header("Location: ../login.html");
    exit;
}

ob_end_flush();
?>
