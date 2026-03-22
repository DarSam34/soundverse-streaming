<?php
// Clase base para la conexion a la BD usando PDO
// La usamos en todos los modelos del proyecto para no repetir el mismo codigo

class Conexion
{
    // Ponemos las credenciales como atributos privados para que nadie
    // pueda acceder a ellas desde afuera de la clase
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "lp3_streaming_musica";

    // El metodo conectar() devuelve el objeto PDO listo para hacer consultas
    // Si falla, guarda el error en el log y devuelve null
    public function conectar()
    {
        try {
            // El DSN lleva el charset utf8mb4 para que no se rompan las tildes
            $dsn = "mysql:host=" . $this->host .
                ";dbname=" . $this->db .
                ";charset=utf8mb4";

            $pdo = new PDO($dsn, $this->user, $this->pass);

            // Hacemos que PDO lance excepciones cuando hay un error SQL
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Esto hace que los fetch() devuelvan arrays asociativos por defecto
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;

        } catch (PDOException $e) {
            $log_file = __DIR__ . "/../logs/errores.log";

            $mensaje = "[" . date('Y-m-d H:i:s') . "] Error de conexion: " .
                $e->getMessage() . " en " . $e->getFile() .
                " linea " . $e->getLine() . PHP_EOL;

            file_put_contents($log_file, $mensaje, FILE_APPEND);

            return null;
        }
    }
}

?>