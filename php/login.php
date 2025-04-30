<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 1) Sesión segura
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// 2) Conexión
require 'config.php';

// 3) Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula   = $mysqli->real_escape_string($_POST['cedula']);
    $password = $_POST['password'];

    $sql = "SELECT id, nombre_completo, password, rol, empresa
            FROM users
            WHERE cedula = '$cedula'
            LIMIT 1";
    $res = $mysqli->query($sql);
    if (!$res) {
        error_log("MySQL error: " . $mysqli->error);
        die("Error interno, inténtalo más tarde.");
    }

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Guardar en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre']  = $user['nombre_completo'];
            $_SESSION['rol']     = $user['rol'];
            $_SESSION['empresa'] = $user['empresa'];

            // Redirigir según rol
            if ($user['rol'] === 'admin') {
                header("Location: ../php/dashboard_admin.php");
            } elseif ($user['rol'] === 'profesor') {
                header("Location: ../php/dashboard_profesor.php");
            } else {
                header("Location: ../php/dashboard_estudiante.php");
            }
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No existe ningún usuario con esa cédula.";
    }
} else {
    header("Location: ../login.html");
    exit;
}

// Si hubo error, lo mostramos y detenemos
if (!empty($error)) {
    echo "<p style='color:red; text-align:center; margin-top:20px;'>$error</p>";
    exit;
}

ob_end_flush();
