<?php
/**
 * ARCHIVO: admin/php/queries.php
 * PROPÓSITO: Procesar registros con la estructura exacta y validación de llaves foráneas.
 */

header('Content-Type: application/json');
error_reporting(E_ALL); 
ini_set('display_errors', 0); 

$ruta_conexion = dirname(__DIR__, 2) . "/classes/conexion.php";

if (!file_exists($ruta_conexion)) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el archivo de conexión.']);
    exit;
}

require_once $ruta_conexion;

$database = new Conexion();
$db = $database->conectar();

if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'La base de datos no respondió.']);
    exit;
}

if (isset($_POST['accion']) && $_POST['accion'] === 'registrarUsuario') {
    
    $nombre = $_POST['nombre'] ?? '';
    $email  = $_POST['email'] ?? '';
    $pass   = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    
    // =========================================================================
    // [SOLUCIÓN AL ERROR 1452]: Traducción de texto a Llave Foránea
    // =========================================================================
    $rol_recibido = $_POST['rol'] ?? ''; 
    
    // Si el formulario manda "Admin", "Premium" o el número 2, le asignamos Premium (2)
    // Para cualquier otro caso ("Cliente", vacío, etc.), le asignamos Free (1)
    if ($rol_recibido === 'Admin' || $rol_recibido === 'Premium' || $rol_recibido == 2) {
        $id_tipo = 2; // Corresponde al ID de Premium en Tipo_Suscripcion
    } else {
        $id_tipo = 1; // Corresponde al ID de Free en Tipo_Suscripcion
    }

    try {
        $sql = "INSERT INTO Usuario (FK_id_tipo, nombre_completo, correo, clave_hash) 
                VALUES (:tipo, :nom, :ema, :pas)";
        
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(':tipo', $id_tipo);
        $stmt->bindParam(':nom', $nombre);
        $stmt->bindParam(':ema', $email);
        $stmt->bindParam(':pas', $pass);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => '¡Éxito! ' . $nombre . ' se registró en Soundverse.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error interno al insertar en la tabla.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
    }

// =========================================================================
// BLOQUE: ELIMINAR USUARIO
// =========================================================================
} elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminarUsuario') {
    
    $id_usuario = $_POST['id_usuario'] ?? '';

    try {
        $sql = "DELETE FROM Usuario WHERE PK_id_usuario = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id_usuario);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'El usuario ha sido eliminado del sistema.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar al usuario.']);
        }
    } catch (PDOException $e) {
        // Protege contra borrado si el usuario ya tiene datos enlazados
        echo json_encode(['status' => 'error', 'message' => 'Error de BD: El usuario tiene datos enlazados y no puede borrarse.']);
    }

// =========================================================================
// [NUEVA MODIFICACIÓN]: INICIAR SESIÓN (LOGIN)
// =========================================================================
} elseif (isset($_POST['accion']) && $_POST['accion'] === 'iniciarSesion') {
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Buscamos si el correo existe en la base de datos
        $sql = "SELECT PK_id_usuario, nombre_completo, clave_hash FROM Usuario WHERE correo = :email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Si encontramos el correo...
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificamos si la contraseña escrita coincide con la encriptada (Hash)
            if (password_verify($password, $usuario['clave_hash'])) {
                
                // ¡Contraseña correcta! Creamos las variables de sesión
                session_start();
                $_SESSION['usuario_id'] = $usuario['PK_id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre_completo'];
                
                echo json_encode(['status' => 'success', 'message' => 'Acceso concedido.']);
            } else {
                // Contraseña mala
                echo json_encode(['status' => 'error', 'message' => 'La contraseña es incorrecta.']);
            }
        } else {
            // El correo no existe
            echo json_encode(['status' => 'error', 'message' => 'Este correo no está registrado en el sistema.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error de conexión al verificar el usuario.']);
    }

// =========================================================================
// BLOQUE: CATÁLOGO MUSICAL (CANCIONES)
// =========================================================================
} elseif (isset($_POST['accion']) && $_POST['accion'] === 'listar_canciones') {
    require_once dirname(__DIR__, 2) . "/classes/Cancion.php";
    $cancionObj = new Cancion();
    echo json_encode($cancionObj->listarCanciones());

} elseif (isset($_POST['accion']) && $_POST['accion'] === 'datos_selects_cancion') {
    require_once dirname(__DIR__, 2) . "/classes/Cancion.php";
    $cancionObj = new Cancion();
    echo json_encode([
        'albumes' => $cancionObj->listarAlbumes(),
        'generos' => $cancionObj->listarGeneros()
    ]);

} elseif (isset($_POST['accion']) && $_POST['accion'] === 'guardar_cancion') {
    require_once dirname(__DIR__, 2) . "/classes/Cancion.php";
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
        echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'Canción actualizada exitosamente.' : 'Error al actualizar la canción en la BD.']);
    }

} elseif (isset($_POST['accion']) && $_POST['accion'] === 'obtener_cancion') {
    require_once dirname(__DIR__, 2) . "/classes/Cancion.php";
    $cancionObj = new Cancion();
    $id = $_POST['id'] ?? '';
    echo json_encode($cancionObj->obtenerCancion($id));

} elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_cancion') {
    require_once dirname(__DIR__, 2) . "/classes/Cancion.php";
    $cancionObj = new Cancion();
    $id = $_POST['id'] ?? '';
    $res = $cancionObj->eliminarCancion($id);
    // Recuerda que aquí usamos borrado lógico
    echo json_encode(['status' => $res ? 'success' : 'error', 'message' => $res ? 'La canción fue dada de baja (Borrado lógico).' : 'Error al eliminar la canción.']);

// =========================================================================
// ACCIÓN DESCONOCIDA
// =========================================================================
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió una acción válida desde el formulario.']);
}

?>