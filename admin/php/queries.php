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
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió una acción válida desde el formulario.']);
}
?>