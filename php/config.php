<?php
// config.php
$host = 'localhost';
$user = 'root';
$password = ''; // Cambia la contraseña si la has modificado en XAMPP
$dbname = 'cursos_db';
$port = 3306;

$mysqli = new mysqli($host, $user, $password, $dbname, $port);

if ($mysqli->connect_error) {
    die('Error de conexión (' . $mysqli->connect_errno . '): ' . $mysqli->connect_error);
}
?>
