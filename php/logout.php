<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',       // O 'localhost' si lo prefieres
    'secure'   => false,    // false, porque usas HTTP, no HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

session_destroy();
header("Location: ../login.html");
exit;
?>
