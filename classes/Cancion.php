<?php
require_once __DIR__ . '/Conexion.php';

// Modelo para todo lo relacionado a canciones
// Separo esto de queries.php para mantener la logica de BD en un solo lugar

class Cancion extends Conexion
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->conectar();
    }

    // Traemos solo las canciones activas con el JOIN para ver artista, album y genero
    public function listarCanciones()
    {
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

    // Para llenar los <select> del formulario de canciones
    public function listarAlbumes()
    {
        $stmt = $this->db->prepare("SELECT PK_id_album, titulo FROM Album WHERE estado_disponible = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarGeneros()
    {
        $stmt = $this->db->prepare("SELECT PK_id_genero, nombre_genero FROM Genero_Musical");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar cancion nueva
    public function registrarCancion($id_album, $id_genero, $titulo, $duracion, $ruta, $letra)
    {
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

    // Buscar una cancion por ID para cargar sus datos en el formulario de edicion
    public function obtenerCancion($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM Cancion WHERE PK_id_cancion = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar datos de una cancion existente
    public function actualizarCancion($id, $id_album, $id_genero, $titulo, $duracion, $ruta, $letra)
    {
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

    // Borrado logico igual que en usuarios: no borramos la fila, solo la desactivamos
    public function eliminarCancion($id)
    {
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