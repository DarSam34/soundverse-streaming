<?php
require_once __DIR__ . '/Conexion.php';

/*
 * Archivo: Usuario.php
 * Aqui van todos los metodos relacionados a usuarios.
 * El objetivo es separar el SQL de queries.php para que no se mezcle
 * todo en un solo archivo, que fue lo que nos explico el Lic. Obed con POO.
 */

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->conectar();
    }

    public function cerrarConexion()
    {
        $this->db = null;
    }

    // Antes de insertar un usuario revisamos si el correo ya existe
    // para evitar duplicados en la tabla
    public function verificarCorreoExistente($email)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM Usuario WHERE correo = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ((int) $resultado['total']) > 0;

        } catch (PDOException $e) {
            $this->logError('verificarCorreoExistente(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // Registrar usuario nuevo. La contrasena se hashea aqui
    // porque el controlador no deberia saber nada de eso (encapsulamiento)
    public function guardarUsuario($id_tipo, $nombre, $email, $password)
    {
        try {
            $clave_hash = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO Usuario (FK_id_tipo, nombre_completo, correo, clave_hash)
                     VALUES (:tipo, :nombre, :email, :hash)";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':tipo', $id_tipo, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':hash', $clave_hash);

            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            $this->logError('guardarUsuario(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // En vez de hacer DELETE lo que hacemos es poner estado_disponible = 0
    // asi el registro sigue en la BD pero ya no aparece en las consultas normales
    public function eliminarUsuarioLogico($id_usuario)
    {
        try {
            $sql = "UPDATE Usuario SET estado_disponible = 0 WHERE PK_id_usuario = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            $this->logError('eliminarUsuarioLogico(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // Login: buscamos el correo en la BD y con password_verify
    // comparamos lo que escribio el usuario con el hash guardado
    public function login($email, $password)
    {
        try {
            $sql = "SELECT PK_id_usuario, nombre_completo, clave_hash
                     FROM Usuario
                     WHERE correo = :email AND estado_disponible = 1
                     LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $usuario['clave_hash'])) {
                    return $usuario;
                }
            }

            return false;

        } catch (PDOException $e) {
            $this->logError('login(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // Trae todos los usuarios activos junto con su tipo de plan
    // para mostrarlos en la tabla del panel de administracion
    public function listarUsuarios()
    {
        try {
            $sql = "SELECT u.PK_id_usuario, u.nombre_completo, u.correo,
                            u.FK_id_tipo, t.nombre_plan
                     FROM Usuario u
                     INNER JOIN Tipo_Suscripcion t ON u.FK_id_tipo = t.PK_id_tipo
                     WHERE u.estado_disponible = 1
                     ORDER BY u.PK_id_usuario DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $this->logError('listarUsuarios(): ' . $e->getMessage());
            return [];
        } finally {
            $this->db = null;
        }
    }

    // Actualizar datos del usuario. Si no mandan nueva contrasena
    // simplemente no se incluye en el UPDATE para no pisar la anterior
    public function actualizarUsuario($id_usuario, $id_tipo, $nombre, $email, $pass = '')
    {
        try {
            $sql = "UPDATE Usuario SET FK_id_tipo = ?, nombre_completo = ?, correo = ?";
            $params = [$id_tipo, $nombre, $email];

            if (!empty($pass)) {
                $clave_hash = password_hash($pass, PASSWORD_DEFAULT);
                $sql .= ", clave_hash = ?";
                $params[] = $clave_hash;
            }

            $sql .= " WHERE PK_id_usuario = ?";
            $params[] = $id_usuario;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();

        } catch (PDOException $e) {
            $this->logError('actualizarUsuario(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // El Lic. dijo que jamas hay que mostrar el error de BD al usuario final
    // por eso lo guardamos en un archivo de log
    private function logError($mensaje)
    {
        $ruta = __DIR__ . '/../logs/errores.log';
        $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
        file_put_contents($ruta, $linea, FILE_APPEND);
    }
}
?>