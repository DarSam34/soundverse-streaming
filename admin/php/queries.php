<?php
/**
 * ARCHIVO: admin/php/queries.php
 * PROPÓSITO: Controlador único central para las peticiones AJAX manejadas por un switch() sobre $_GET['caso'].
 */

// Iniciar sesión y preparativos
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

// CAPTURAR EL CASO ENVIADO POR LA URL
$caso = $_GET['caso'] ?? '';

// SWITCH PRINCIPAL REQUERIDO POR EL LIC. OBED
switch ($caso) {
    // CASOS NO PROTEGIDOS
    case 'iniciarSesion':
        iniciarSesion();
        break;

    // CASOS PROTEGIDOS (REQUiEREN SESIÓN ACTIVA)
    case 'registrarUsuario':
        verificarSesionAjax();
        registrarUsuario();
        break;

    case 'actualizarUsuario':
        verificarSesionAjax();
        actualizarUsuario();
        break;

    case 'eliminarUsuario':
        verificarSesionAjax();
        eliminarUsuario();
        break;

    case 'listar_usuarios':
        verificarSesionAjax();
        listarUsuarios();
        break;

    case 'listar_canciones':
        verificarSesionAjax();
        listarCanciones();
        break;

    case 'datos_selects_cancion':
        verificarSesionAjax();
        datosSelectsCancion();
        break;

    case 'guardar_cancion':
        verificarSesionAjax();
        guardarCancion();
        break;

    case 'obtener_cancion':
        verificarSesionAjax();
        obtenerCancion();
        break;

    case 'eliminar_cancion':
        verificarSesionAjax();
        eliminarCancion();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Caso no válido o vacío procesado por el controlador.']);
        break;
}

// =========================================================================
// DEFINICIÓN DE FUNCIONES PARA EL CONTROLADOR
// =========================================================================

function verificarSesionAjax()
{
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sesión no válida o expirada.']);
        exit();
    }
}

function iniciarSesion()
{
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $usuarioObj = new Usuario();
    $usuario = $usuarioObj->login($email, $password);

    if ($usuario !== false) {
        // Renovar y limpiar sesión previe
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['PK_id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['time'] = time();

        echo json_encode(['status' => 'success', 'message' => 'Acceso concedido. Redirigiendo...']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Correo o contraseña incorrectos.']);
    }
}

function registrarUsuario()
{
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    // Rol: si viene '2' es Premium, sino '1' (Free)
    $id_tipo = (isset($_POST['rol']) && $_POST['rol'] == 2) ? 2 : 1;

    if (strlen($pass) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres por seguridad.']);
        exit;
    }

    $usuarioObj = new Usuario();

    if ($usuarioObj->verificarCorreoExistente($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Este correo ya está registrado en el sistema.']);
    } else {
        // Se crea nueva instancia porque verificarCorreoExistente() cerró la conexión PDO en su finally()
        $usuarioObj2 = new Usuario();
        $resultado = $usuarioObj2->guardarUsuario($id_tipo, $nombre, $email, $pass);
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => '¡Éxito! El usuario se registró correctamente en Soundverse.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error de BD interno al intentar guardar el usuario.']);
        }
    }
}

function actualizarUsuario()
{
    $id_usuario = (int) ($_POST['id_usuario'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $id_tipo = (isset($_POST['rol']) && $_POST['rol'] == 2) ? 2 : 1;

    if (!empty($pass) && strlen($pass) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
        exit;
    }

    $usuarioObj = new Usuario();
    // NOTA: if $pass is empty, actualizarUsuario doesn't update the password hash
    $resultado = $usuarioObj->actualizarUsuario($id_usuario, $id_tipo, $nombre, $email, $pass);

    if ($resultado !== false) { // PDO might return 0 modified rows if data didn't change, which is still a success
        echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el usuario. Verifique el log de errores.']);
    }
}

function eliminarUsuario()
{
    $id_usuario = (int) ($_POST['id_usuario'] ?? 0);
    $usuarioObj = new Usuario();
    $resultado = $usuarioObj->eliminarUsuarioLogico($id_usuario);

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'El usuario fue dado de baja del sistema (Borrado lógico).']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo dar de baja al usuario. Posiblemente no existe.']);
    }
}

function listarUsuarios()
{
    // Si la vista ya hiciera una carga directa en PHP, esto sería innecesario, pero lo dejamos por si piden una grilla AJAX.
    $usuarioObj = new Usuario();
    echo json_encode($usuarioObj->listarUsuarios());
}

// ========================== MÓDULO CANCIONES ==========================

function listarCanciones()
{
    $cancionObj = new Cancion();
    echo json_encode($cancionObj->listarCanciones());
}

function datosSelectsCancion()
{
    $cancionObj = new Cancion();
    echo json_encode([
        'albumes' => $cancionObj->listarAlbumes(),
        'generos' => $cancionObj->listarGeneros()
    ]);
}

function guardarCancion()
{
    $cancionObj = new Cancion();
    $id = $_POST['id_cancion'] ?? '';
    $album = $_POST['album'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $duracion = $_POST['duracion_segundos'] ?? '';
    $ruta = $_POST['ruta_archivo_audio'] ?? '';
    $letra = $_POST['letra_sincronizada'] ?? '';

    if (empty($id) || $id == '0') {
        $res = $cancionObj->registrarCancion($album, $genero, $titulo, $duracion, $ruta, $letra);
        echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'Canción registrada exitosamente.' : 'Error al registrar la canción en la BD.']);
    } else {
        $res = $cancionObj->actualizarCancion($id, $album, $genero, $titulo, $duracion, $ruta, $letra);
        echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'Canción actualizada exitosamente.' : 'Error al actualizar la canción.']);
    }
}

function obtenerCancion()
{
    $cancionObj = new Cancion();
    $id = $_POST['id'] ?? '';
    echo json_encode($cancionObj->obtenerCancion($id));
}

function eliminarCancion()
{
    $cancionObj = new Cancion();
    $id = $_POST['id'] ?? '';
    $res = $cancionObj->eliminarCancion($id);
    echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'La canción fue dada de baja (Borrado lógico).' : 'Error al eliminar la canción.']);
}
?>