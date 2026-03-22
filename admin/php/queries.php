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

// Leemos el caso que manda el JS desde la URL (?caso=...)
$caso = $_GET['caso'] ?? '';

switch ($caso) {
    case 'iniciarSesion':
        iniciarSesion();
        break;

    // Los siguientes casos requieren sesion activa
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
        echo json_encode(['status' => 'error', 'message' => 'Caso no valido o vacio procesado por el controlador.']);
        break;
}

// Verifica que haya sesion activa antes de ejecutar cualquier caso protegido
function verificarSesionAjax()
{
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sesion no valida o expirada.']);
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
        // Regeneramos el ID de sesion por seguridad antes de guardar los datos
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['PK_id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['time'] = time();

        echo json_encode(['status' => 'success', 'message' => 'Acceso concedido. Redirigiendo...']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Correo o contrasena incorrectos.']);
    }
}

function registrarUsuario()
{
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    // Si viene '2' es Premium, de lo contrario se asigna Free
    $id_tipo = (isset($_POST['rol']) && $_POST['rol'] == 2) ? 2 : 1;

    if (strlen($pass) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'La contrasena debe tener al menos 8 caracteres por seguridad.']);
        exit;
    }

    $usuarioObj = new Usuario();

    if ($usuarioObj->verificarCorreoExistente($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Este correo ya esta registrado en el sistema.']);
    } else {
        // Se necesita nueva instancia porque verificarCorreoExistente() cierra la conexion PDO en su finally()
        $usuarioObj2 = new Usuario();
        $resultado = $usuarioObj2->guardarUsuario($id_tipo, $nombre, $email, $pass);
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'El usuario se registro correctamente en Soundverse.']);
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
        echo json_encode(['status' => 'error', 'message' => 'La nueva contrasena debe tener al menos 8 caracteres.']);
        exit;
    }

    $usuarioObj = new Usuario();
    // Si $pass viene vacio, actualizarUsuario no toca el hash de la clave
    $resultado = $usuarioObj->actualizarUsuario($id_usuario, $id_tipo, $nombre, $email, $pass);

    if ($resultado !== false) {
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
        echo json_encode(['status' => 'success', 'message' => 'El usuario fue dado de baja del sistema (Borrado logico).']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo dar de baja al usuario. Posiblemente no existe.']);
    }
}

function listarUsuarios()
{
    $usuarioObj = new Usuario();
    echo json_encode($usuarioObj->listarUsuarios());
}

// Modulo de Canciones

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
        echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'Cancion registrada exitosamente.' : 'Error al registrar la cancion en la BD.']);
    } else {
        $res = $cancionObj->actualizarCancion($id, $album, $genero, $titulo, $duracion, $ruta, $letra);
        echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'Cancion actualizada exitosamente.' : 'Error al actualizar la cancion.']);
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
    echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'La cancion fue dada de baja (Borrado logico).' : 'Error al eliminar la cancion.']);
}
?>