<?php
// Clase base para la conexion a la BD usando PDO
// Basada en los apuntes del Lic. Obed del 11 y 16 de febrero
// La usamos en todos los modelos para no repetir la logica de conexion

class Conexion
{
    // Credenciales privadas para que nadie pueda acceder a ellas desde afuera
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "lp3_streaming_musica";

    // conectar() devuelve el objeto PDO listo para hacer consultas
    // Si falla, guarda el error en el log y devuelve null
    public function conectar()
    {
        try {
            // El DSN lleva charset=utf8mb4 para que no se rompan las tildes y simbolos
            $dsn = "mysql:host=" . $this->host .
                ";dbname=" . $this->db .
                ";charset=utf8mb4";

            $pdo = new PDO($dsn, $this->user, $this->pass);

            // Que PDO lance excepciones cuando hay un error SQL
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Asi los fetch() devuelven arrays asociativos por defecto sin tener que pedirlo cada vez
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