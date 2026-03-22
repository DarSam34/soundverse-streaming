<?php
// Controlador central de peticiones AJAX
// Todas las peticiones del frontend llegan aqui y se enrutan segun $_GET['caso']
// El Lic. pidio que usaramos switch() con GET en vez de if/else con POST

// Iniciamos sesion y le decimos al navegador que esperamos JSON de respuesta
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$ruta_conexion = dirname(__DIR__, 2) . '/classes/Conexion.php';
$ruta_usuario = dirname(__DIR__, 2) . '/classes/Usuario.php';
$ruta_cancion = dirname(__DIR__, 2) . '/classes/Cancion.php';

if (!file_exists($ruta_conexion) || !file_exists($ruta_usuario)) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el archivo de clases en el servidor.']);
    exit;
}

require_once $ruta_conexion;
require_once $ruta_usuario;
require_once $ruta_cancion;

$database = new Conexion();
$db = $database->conectar();

if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'La base de datos no respondió.']);
    exit;
}

?>