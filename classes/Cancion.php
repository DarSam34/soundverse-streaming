<?php
require_once 'conexion.php';

<<<<<<< HEAD
class Cancion extends Conexion
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->conectar();
    }

    // Listar canciones activas
    public function listarCanciones()
    {
=======
class Cancion extends Conexion {
    private $db;

    public function __construct() {
        $this->db = clone $this->conectar();
    }

    // Listar canciones activas
    public function listarCanciones() {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        try {
            $sql = "SELECT c.PK_id_cancion, c.titulo, c.duracion_segundos, c.ruta_archivo_audio, 
                           c.contador_reproducciones, a.titulo as album, ar.nombre_artistico as artista, 
                           g.nombre_genero as genero 
                    FROM Cancion c
                    INNER JOIN Album a ON c.FK_id_album = a.PK_id_album
                    INNER JOIN Artista ar ON a.FK_id_artista = ar.PK_id_artista
                    INNER JOIN Genero_Musical g ON c.FK_id_genero = g.PK_id_genero
                    WHERE c.estado_disponible = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Listar álbumes y géneros para los <select> del formulario
<<<<<<< HEAD
    public function listarAlbumes()
    {
=======
    public function listarAlbumes() {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        $stmt = $this->db->prepare("SELECT PK_id_album, titulo FROM Album WHERE estado_disponible = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

<<<<<<< HEAD
    public function listarGeneros()
    {
=======
    public function listarGeneros() {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        $stmt = $this->db->prepare("SELECT PK_id_genero, nombre_genero FROM Genero_Musical");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear canción
<<<<<<< HEAD
    public function registrarCancion($id_album, $id_genero, $titulo, $duracion, $ruta, $letra)
    {
=======
    public function registrarCancion($id_album, $id_genero, $titulo, $duracion, $ruta, $letra) {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        try {
            $sql = "INSERT INTO Cancion (FK_id_album, FK_id_genero, titulo, duracion_segundos, ruta_archivo_audio, letra_sincronizada) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_album, $id_genero, $titulo, $duracion, $ruta, $letra]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener una canción para editar
<<<<<<< HEAD
    public function obtenerCancion($id)
    {
=======
    public function obtenerCancion($id) {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        $stmt = $this->db->prepare("SELECT * FROM Cancion WHERE PK_id_cancion = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar canción
<<<<<<< HEAD
    public function actualizarCancion($id, $id_album, $id_genero, $titulo, $duracion, $ruta, $letra)
    {
=======
    public function actualizarCancion($id, $id_album, $id_genero, $titulo, $duracion, $ruta, $letra) {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        try {
            $sql = "UPDATE Cancion SET FK_id_album = ?, FK_id_genero = ?, titulo = ?, 
                    duracion_segundos = ?, ruta_archivo_audio = ?, letra_sincronizada = ? 
                    WHERE PK_id_cancion = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_album, $id_genero, $titulo, $duracion, $ruta, $letra, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Borrado Lógico
<<<<<<< HEAD
    public function eliminarCancion($id)
    {
=======
    public function eliminarCancion($id) {
>>>>>>> 5094ee0b09a9b22f47c1f31c8524dd2f3c5e88d5
        try {
            $sql = "UPDATE Cancion SET estado_disponible = 0 WHERE PK_id_cancion = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>