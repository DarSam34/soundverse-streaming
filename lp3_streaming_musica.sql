-- ==============================================================================
-- PLATAFORMA DE STREAMING DE MÚSICA "SOUNDVERSE" (PROYECTO 6)
-- Script completo con datos de prueba para Construcción Parte #1
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS lp3_streaming_musica
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lp3_streaming_musica;

-- ==============================================================================
-- NIVEL 1: CONFIGURACIÓN Y SOPORTE
-- ==============================================================================

CREATE TABLE Tipo_Suscripcion (
    PK_id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_plan VARCHAR(30) NOT NULL,
    precio_mensual DECIMAL(10,2) NOT NULL DEFAULT 0,
    calidad_kbps INT NOT NULL DEFAULT 128,
    limite_playlists INT NOT NULL DEFAULT 15,
    limite_skips_hora INT NOT NULL DEFAULT 6,
    puede_descargar TINYINT(1) DEFAULT 0,
    tiene_anuncios TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Metodo_Pago (
    PK_id_metodo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_metodo VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Genero_Musical (
    PK_id_genero INT AUTO_INCREMENT PRIMARY KEY,
    nombre_genero VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE Idioma_Traduccion (
    PK_id_idioma INT AUTO_INCREMENT PRIMARY KEY,
    codigo_iso VARCHAR(5) NOT NULL,
    etiqueta_llave VARCHAR(100) NOT NULL,
    texto_traduccion TEXT NOT NULL,
    UNIQUE KEY unique_etiqueta_idioma (codigo_iso, etiqueta_llave)
) ENGINE=InnoDB;

-- ==============================================================================
-- NIVEL 2: ACTORES DEL SISTEMA
-- ==============================================================================

CREATE TABLE Usuario (
    PK_id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_tipo INT NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    clave_hash VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_disponible TINYINT(1) DEFAULT 1,
    FOREIGN KEY (FK_id_tipo) REFERENCES Tipo_Suscripcion(PK_id_tipo)
) ENGINE=InnoDB;

CREATE TABLE Artista (
    PK_id_artista INT AUTO_INCREMENT PRIMARY KEY,
    nombre_artistico VARCHAR(100) NOT NULL,
    biografia TEXT,
    ruta_foto_perfil VARCHAR(255),
    verificado TINYINT(1) DEFAULT 0,
    estado_disponible TINYINT(1) DEFAULT 1,
    FK_id_usuario_gestor INT NULL,
    FOREIGN KEY (FK_id_usuario_gestor) REFERENCES Usuario(PK_id_usuario)
) ENGINE=InnoDB;

-- ==============================================================================
-- NIVEL 3: CATÁLOGO MULTIMEDIA
-- ==============================================================================

CREATE TABLE Album (
    PK_id_album INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_artista INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    fecha_lanzamiento DATE NOT NULL,
    ruta_portada VARCHAR(255),
    estado_disponible TINYINT(1) DEFAULT 1,
    FOREIGN KEY (FK_id_artista) REFERENCES Artista(PK_id_artista)
) ENGINE=InnoDB;

CREATE TABLE Cancion (
    PK_id_cancion INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_album INT NOT NULL,
    FK_id_genero INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    duracion_segundos INT NOT NULL,
    ruta_archivo_audio VARCHAR(255) NOT NULL,
    letra_sincronizada TEXT,
    contador_reproducciones INT DEFAULT 0,
    estado_disponible TINYINT(1) DEFAULT 1,
    FOREIGN KEY (FK_id_album) REFERENCES Album(PK_id_album),
    FOREIGN KEY (FK_id_genero) REFERENCES Genero_Musical(PK_id_genero)
) ENGINE=InnoDB;

-- ==============================================================================
-- NIVEL 4: INTERACCIONES
-- ==============================================================================

CREATE TABLE Playlist (
    PK_id_playlist INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_usuario INT NOT NULL,
    nombre_playlist VARCHAR(100) NOT NULL,
    visibilidad ENUM('Publica', 'Privada', 'Colaborativa') DEFAULT 'Publica',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_disponible TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_playlist_usuario (FK_id_usuario, nombre_playlist),
    FOREIGN KEY (FK_id_usuario) REFERENCES Usuario(PK_id_usuario)
) ENGINE=InnoDB;

CREATE TABLE Detalle_Playlist (
    FK_id_playlist INT NOT NULL,
    FK_id_cancion INT NOT NULL,
    fecha_agregada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    orden_reproduccion INT,
    PRIMARY KEY (FK_id_playlist, FK_id_cancion),
    FOREIGN KEY (FK_id_playlist) REFERENCES Playlist(PK_id_playlist) ON DELETE CASCADE,
    FOREIGN KEY (FK_id_cancion) REFERENCES Cancion(PK_id_cancion)
) ENGINE=InnoDB;

CREATE TABLE Historial_Reproduccion (
    PK_id_historial INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_usuario INT NOT NULL,
    FK_id_cancion INT NOT NULL,
    segundos_escuchados INT NOT NULL,
    es_valida_regalia TINYINT(1) DEFAULT 0,
    fecha_hora_reproduccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (FK_id_usuario) REFERENCES Usuario(PK_id_usuario),
    FOREIGN KEY (FK_id_cancion) REFERENCES Cancion(PK_id_cancion)
) ENGINE=InnoDB;

-- ==============================================================================
-- NIVEL 5: FACTURACIÓN
-- ==============================================================================

CREATE TABLE Factura (
    PK_id_factura INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_usuario INT NOT NULL,
    FK_id_metodo INT NOT NULL,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    monto_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (FK_id_usuario) REFERENCES Usuario(PK_id_usuario),
    FOREIGN KEY (FK_id_metodo) REFERENCES Metodo_Pago(PK_id_metodo)
) ENGINE=InnoDB;

CREATE TABLE Detalle_Factura (
    PK_id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_factura INT NOT NULL,
    FK_id_tipo_suscripcion INT NOT NULL,
    precio_aplicado DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (FK_id_factura) REFERENCES Factura(PK_id_factura) ON DELETE CASCADE,
    FOREIGN KEY (FK_id_tipo_suscripcion) REFERENCES Tipo_Suscripcion(PK_id_tipo)
) ENGINE=InnoDB;

-- ==============================================================================
-- NIVEL 6: SEGUIMIENTO
-- ==============================================================================

CREATE TABLE Seguimiento_Artista (
    FK_id_usuario INT NOT NULL,
    FK_id_artista INT NOT NULL,
    fecha_seguimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (FK_id_usuario, FK_id_artista),
    FOREIGN KEY (FK_id_usuario) REFERENCES Usuario(PK_id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (FK_id_artista) REFERENCES Artista(PK_id_artista) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Notificacion (
    PK_id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    FK_id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    leida TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo VARCHAR(50),
    referencia_id INT NULL,
    FOREIGN KEY (FK_id_usuario) REFERENCES Usuario(PK_id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================================
-- DATOS DE PRUEBA
-- ==============================================================================

-- Tipos de suscripción
INSERT INTO Tipo_Suscripcion (nombre_plan, precio_mensual, calidad_kbps, limite_playlists, limite_skips_hora, puede_descargar, tiene_anuncios) VALUES
('Free', 0.00, 128, 15, 6, 0, 1),
('Premium', 149.00, 320, 9999, 9999, 1, 0);

-- Métodos de pago
INSERT INTO Metodo_Pago (nombre_metodo) VALUES
('Tarjeta de Crédito'),
('PayPal'),
('Transferencia Bancaria');

-- Géneros musicales
INSERT INTO Genero_Musical (nombre_genero) VALUES
('Rock'), ('Pop'), ('Hip Hop'), ('Electrónica'), ('Clásica'), ('Jazz'), ('Reggaetón'), ('Salsa');

-- USUARIOS DE PRUEBA (contraseña: Lp3.2026)
-- IMPORTANTE: Ejecuta este PHP para generar los hashes reales:
-- <?php echo password_hash('Lp3.2026', PASSWORD_DEFAULT); ?>
-- Luego reemplaza los hashes de abajo
INSERT INTO Usuario (FK_id_tipo, nombre_completo, correo, clave_hash) VALUES
(1, 'Usuario Free', 'free@test.com', '$2y$10$MDuw3m.jxWbYA/rZypiQs.Z2i.emuobdF4BeAAqBOC1r/a/A0XcHu'),
(2, 'Usuario Premium', 'premium@test.com', '$2y$10$MDuw3m.jxWbYA/rZypiQs.Z2i.emuobdF4BeAAqBOC1r/a/A0XcHu');

-- Artistas
INSERT INTO Artista (nombre_artistico, biografia, verificado) VALUES
('The Beatles', 'Banda británica de rock formada en Liverpool en 1960.', 1),
('Adele', 'Cantante y compositora británica.', 1),
('Daft Punk', 'Dúo francés de música electrónica.', 1);

-- Álbumes
INSERT INTO Album (FK_id_artista, titulo, fecha_lanzamiento, ruta_portada) VALUES
(1, 'Abbey Road', '1969-09-26', '/assets/portadas/abbey_road.jpg'),
(1, 'Let It Be', '1970-05-08', '/assets/portadas/let_it_be.jpg'),
(2, '21', '2011-01-24', '/assets/portadas/21.jpg'),
(3, 'Random Access Memories', '2013-05-17', '/assets/portadas/ram.jpg');

-- Canciones
INSERT INTO Cancion (FK_id_album, FK_id_genero, titulo, duracion_segundos, ruta_archivo_audio) VALUES
(1, 1, 'Come Together', 259, '/assets/musica/come_together.mp3'),
(1, 1, 'Something', 182, '/assets/musica/something.mp3'),
(2, 1, 'Let It Be', 243, '/assets/musica/let_it_be.mp3'),
(3, 2, 'Rolling in the Deep', 228, '/assets/musica/rolling_in_the_deep.mp3'),
(3, 2, 'Someone Like You', 285, '/assets/musica/someone_like_you.mp3'),
(4, 4, 'Get Lucky', 369, '/assets/musica/get_lucky.mp3');

-- Playlists
INSERT INTO Playlist (FK_id_usuario, nombre_playlist, visibilidad) VALUES
(1, 'Mis favoritas', 'Publica'),
(2, 'Para entrenar', 'Privada');

-- Detalle de playlists
INSERT INTO Detalle_Playlist (FK_id_playlist, FK_id_cancion, orden_reproduccion) VALUES
(1, 1, 1), (1, 4, 2), (2, 2, 1), (2, 6, 2);

-- Historial
INSERT INTO Historial_Reproduccion (FK_id_usuario, FK_id_cancion, segundos_escuchados, es_valida_regalia) VALUES
(1, 1, 45, 1), (1, 2, 120, 1), (1, 3, 30, 1),
(2, 4, 200, 1), (2, 5, 60, 1), (2, 6, 5, 0);

-- Seguimientos
INSERT INTO Seguimiento_Artista (FK_id_usuario, FK_id_artista) VALUES
(1, 1), (1, 2), (2, 2), (2, 3);