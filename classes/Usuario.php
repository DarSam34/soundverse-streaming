<?php
/**
 * CLASE: Usuario
 * PROPÓSITO:
 * Gestionar todas las operaciones de base de datos relacionadas con los
 * usuarios de la plataforma Soundverse (login, registro, borrado lógico).
 * PATRÓN: MVC - Capa Modelo
 * 1. El constructor instancia la conexión PDO a través de la clase Conexion.
 * 2. Método privado logError() registra excepciones en logs/errores.log.
 * 3. Todas las consultas usan try-catch capturando PDOException.
 * 4. Borrado LÓGICO (UPDATE estado_disponible = 0) — DELETE está prohibido.
 * 5. La conexión se destruye al final de cada método público ($this->db = null).
 */

require_once __DIR__ . '/Conexion.php';

class Usuario
{

    /** @var PDO|null Objeto de conexión a la base de datos */
    private $db;

    // CONSTRUCTOR

    /**
     * Establece la conexión a la base de datos al instanciar la clase.
     */
    public function __construct()
    {
        $this->db = (new Conexion())->conectar();
    }

    // MÉTODO PRIVADO: logError

    /**
     * Registra un mensaje de error en el archivo de log del sistema.
     * Los errores NO se muestran al usuario, solo se guardan en el archivo.
     *
     * @param string $mensaje Descripción del error a registrar.
     */
    private function logError($mensaje)
    {
        $log_file = __DIR__ . '/../logs/errores.log';
        $entrada = '[' . date('Y-m-d H:i:s') . '] [Usuario] ' . $mensaje . PHP_EOL;
        file_put_contents($log_file, $entrada, FILE_APPEND);
    }

    // MÉTODO PÚBLICO: verificarCorreoExistente

    /**
     * Comprueba si un correo electrónico ya está registrado en la BD.
     *
     * @param  string $email Correo a verificar.
     * @return bool   true si el correo ya existe, false si está disponible.
     */
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

    // MÉTODO PÚBLICO: guardarUsuario

    /**
     * Registra un nuevo usuario en la BD con la contraseña encriptada.
     * La encriptación se realiza AQUÍ dentro del modelo, no en el controlador.
     *
     * @param  int    $id_tipo  ID del tipo de suscripción (1=Free, 2=Premium).
     * @param  string $nombre   Nombre completo del usuario.
     * @param  string $email    Correo electrónico único.
     * @param  string $password Contraseña en texto plano (se encripta con BCRYPT).
     * @return bool   true si el INSERT fue exitoso, false si falló.
     */
    public function guardarUsuario($id_tipo, $nombre, $email, $password)
    {
        try {
            // La responsabilidad del hash es del modelo, no del controlador
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

    // MÉTODO PÚBLICO: eliminarUsuarioLogico

    /**
     * Desactiva un usuario cambiando su campo estado_disponible a 0.
     *
     * REGLA ESTRICTA: Está PROHIBIDO usar DELETE.
     * Los registros nunca se borran físicamente de la base de datos.
     *
     * @param  int  $id_usuario ID del usuario a desactivar.
     * @return bool true si se actualizó al menos una fila, false si falló.
     */
    public function eliminarUsuarioLogico($id_usuario)
    {
        try {
            $sql = "UPDATE Usuario SET estado_disponible = 0 WHERE PK_id_usuario = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Validación explícita: debe haber afectado al menos 1 fila
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            $this->logError('eliminarUsuarioLogico(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // MÉTODO PÚBLICO: login

    /**
     * Valida las credenciales del usuario contra la base de datos.
     * Usa password_verify() para comparar contra el hash almacenado.
     * Solo permite el acceso a usuarios con estado_disponible = 1 (activos).
     *
     * @param  string       $email    Correo del usuario.
     * @param  string       $password Contraseña en texto plano.
     * @return array|false  Arreglo asociativo con los datos del usuario si las
     *                      credenciales son correctas, false si fallan.
     */
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

                // Comparar contraseña digitada con el hash de la BD
                if (password_verify($password, $usuario['clave_hash'])) {
                    return $usuario; // Credenciales correctas: retorna datos del usuario
                }
            }

            return false; // Correo no existe o contraseña incorrecta

        } catch (PDOException $e) {
            $this->logError('login(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }

    // MÉTODO PÚBLICO: listarUsuarios

    /**
     * Retorna todos los usuarios activos con su tipo de suscripción.
     * Se usa para recargar la tabla vía AJAX después de crear/editar/eliminar.
     *
     * @return array Arreglo de usuarios activos, o arreglo vacío si hay error.
     */
    public function listarUsuarios()
    {
        try {
            // JOIN con Tipo_Suscripcion para mostrar 'Free' o 'Premium' (columna: nombre_plan)
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

    // MÉTODO PÚBLICO: actualizarUsuario

    /**
     * Actualiza los datos de un usuario existente.
     * Si el password viene vacío, NO se modifica la clave actual (por seguridad).
     *
     * @param  int    $id_usuario ID del usuario a modificar.
     * @param  int    $id_tipo    Nuevo tipo de suscripción (1=Free, 2=Premium).
     * @param  string $nombre     Nuevo nombre completo.
     * @param  string $email      Nuevo correo electrónico.
     * @param  string $password   Nueva contraseña (vacío = no cambiar).
     * @return bool   true si la operación fue exitosa, false si falló.
     */
    public function actualizarUsuario($id_usuario, $id_tipo, $nombre, $email, $password)
    {
        try {
            if (empty($password)) {
                // No se toca la contraseña si el campo llega vacío
                $sql = "UPDATE Usuario
                         SET FK_id_tipo = :tipo, nombre_completo = :nombre, correo = :email
                         WHERE PK_id_usuario = :id";
                $stmt = $this->db->prepare($sql);
            } else {
                // Se actualiza también la clave con nuevo hash BCRYPT
                $sql = "UPDATE Usuario
                         SET FK_id_tipo = :tipo, nombre_completo = :nombre,
                             correo = :email, clave_hash = :hash
                         WHERE PK_id_usuario = :id";
                $stmt = $this->db->prepare($sql);
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt->bindParam(':hash', $hash);
            }

            $stmt->bindParam(':tipo', $id_tipo, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // >= 0: si los datos son idénticos MySQL devuelve rowCount 0 pero la operación es válida
            return $stmt->rowCount() >= 0;

        } catch (PDOException $e) {
            $this->logError('actualizarUsuario(): ' . $e->getMessage());
            return false;
        } finally {
            $this->db = null;
        }
    }
}
?>