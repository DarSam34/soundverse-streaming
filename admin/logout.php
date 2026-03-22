<?php
// 1. Encendemos el motor de sesiones para saber quién está conectado
session_start();

// 2. Destruimos todas las variables de sesión registradas
session_destroy();

// 3. Redirigimos al usuario de vuelta a la pantalla de login
header("Location: index.php");
exit;
?>