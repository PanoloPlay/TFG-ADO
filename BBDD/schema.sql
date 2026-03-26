SET FOREIGN_KEY_CHECKS=0;
DROP DATABASE IF EXISTS TFG_ADO_Tienda_Videojuegos;
CREATE DATABASE TFG_ADO_Tienda_Videojuegos CHARACTER SET utf8mb4;
USE TFG_ADO_Tienda_Videojuegos;

-- ----------------------------
-- Idiomas
-- ----------------------------
CREATE TABLE Idiomas (
  id_idioma CHAR(4) NOT NULL,
  idioma VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_idioma),
  UNIQUE (idioma)
) ENGINE=InnoDB;

-- ----------------------------
-- Usuarios
-- ----------------------------
CREATE TABLE Usuarios (
  id_usuario INT NOT NULL AUTO_INCREMENT,
  nombre_usuario VARCHAR(50) NOT NULL,
  nickname VARCHAR(45) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  clave_acceso VARCHAR(255) NOT NULL,
  fecha_registro DATETIME NOT NULL,
  descripcion TEXT,
  id_idioma_principal CHAR(4) NOT NULL,
  id_idioma_secundario CHAR(4),
  PRIMARY KEY (id_usuario, nickname),
  UNIQUE (correo),
  UNIQUE (nickname),
  INDEX (id_idioma_principal),
  INDEX (id_idioma_secundario),
  FOREIGN KEY (id_idioma_principal) REFERENCES Idiomas(id_idioma)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (id_idioma_secundario) REFERENCES Idiomas(id_idioma)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Juegos
-- ----------------------------
CREATE TABLE Juegos (
  id_juego INT NOT NULL AUTO_INCREMENT,
  nombre_juego VARCHAR(100) NOT NULL,
  descripcion TEXT,
  fecha_publicacion DATETIME NOT NULL,
  desarrollador VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2),
  descuento DECIMAL(5,2),
  PRIMARY KEY (id_juego, nombre_juego),
  UNIQUE (nombre_juego)
) ENGINE=InnoDB;

-- ----------------------------
-- IdiomasJuego
-- ----------------------------
CREATE TABLE IdiomasJuego (
  id_idioma_juego INT NOT NULL AUTO_INCREMENT,
  id_juego INT NOT NULL,
  nombre_juego VARCHAR(100) NOT NULL,
  id_idioma CHAR(4) NOT NULL,
  PRIMARY KEY (id_idioma_juego),
  INDEX (id_idioma),
  INDEX (id_juego, nombre_juego),
  FOREIGN KEY (id_idioma) REFERENCES Idiomas(id_idioma),
  FOREIGN KEY (id_juego, nombre_juego) REFERENCES Juegos(id_juego, nombre_juego)
) ENGINE=InnoDB;

-- ----------------------------
-- Valoraciones
-- ----------------------------
CREATE TABLE Valoraciones (
  id_valoracion INT NOT NULL AUTO_INCREMENT,
  id_juego INT NOT NULL,
  nombre_juego VARCHAR(100) NOT NULL,
  id_idioma_comentario CHAR(4) NOT NULL,
  valoracion ENUM('positiva', 'negativa') NOT NULL,
  comentario VARCHAR(500),
  PRIMARY KEY (id_valoracion),
  INDEX (id_idioma_comentario),
  INDEX (id_juego, nombre_juego),
  FOREIGN KEY (id_idioma_comentario) REFERENCES Idiomas(id_idioma),
  FOREIGN KEY (id_juego, nombre_juego) REFERENCES Juegos(id_juego, nombre_juego)
) ENGINE=InnoDB;

-- ----------------------------
-- Logros
-- ----------------------------
CREATE TABLE Logros (
  id_logro INT NOT NULL AUTO_INCREMENT,
  nombre_logro VARCHAR(100) NOT NULL,
  descripcion_logro TEXT,
  id_juego INT NOT NULL,
  nombre_juego VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_logro, id_juego, nombre_juego, nombre_logro),
  INDEX (id_juego, nombre_juego),
  FOREIGN KEY (id_juego, nombre_juego) REFERENCES Juegos(id_juego, nombre_juego)
) ENGINE=InnoDB;

-- ----------------------------
-- Amigos
-- ----------------------------
CREATE TABLE Amigos (
  id_amistad INT NOT NULL AUTO_INCREMENT,
  id_usuario1 INT NOT NULL,
  nickname1 VARCHAR(45) NOT NULL,
  id_usuario2 INT NOT NULL,
  nickname2 VARCHAR(45) NOT NULL,
  estado ENUM('pendiente', 'aceptada'),
  PRIMARY KEY (id_amistad),
  UNIQUE (id_usuario1, id_usuario2),
  INDEX (id_usuario1, nickname1),
  INDEX (id_usuario2, nickname2),
  FOREIGN KEY (id_usuario1, nickname1) REFERENCES Usuarios(id_usuario, nickname),
  FOREIGN KEY (id_usuario2, nickname2) REFERENCES Usuarios(id_usuario, nickname)
) ENGINE=InnoDB;

-- ----------------------------
-- Mensajes
-- ----------------------------
CREATE TABLE Mensajes (
  id_mensaje INT NOT NULL AUTO_INCREMENT,
  id_amistad INT NOT NULL,
  mensaje VARCHAR(1000) NOT NULL,
  fecha_envio DATETIME NOT NULL,
  leido ENUM('si', 'no') NOT NULL DEFAULT 'no',
  id_remitente INT NOT NULL,
  nickname_remitente VARCHAR(45) NOT NULL,
  id_destinatario INT NOT NULL,
  nickname_destinatario VARCHAR(45) NOT NULL,
  PRIMARY KEY (id_mensaje),
  INDEX (id_amistad),
  INDEX (id_remitente, nickname_remitente),
  INDEX (id_destinatario, nickname_destinatario),
  FOREIGN KEY (id_amistad) REFERENCES Amigos(id_amistad) ON DELETE CASCADE,
  FOREIGN KEY (id_remitente, nickname_remitente) REFERENCES Usuarios(id_usuario, nickname),
  FOREIGN KEY (id_destinatario, nickname_destinatario) REFERENCES Usuarios(id_usuario, nickname)
) ENGINE=InnoDB;

-- ----------------------------
-- LogrosUsuario
-- ----------------------------
CREATE TABLE LogrosUsuario (
  id_usuario_logro INT NOT NULL AUTO_INCREMENT,
  id_usuario INT NOT NULL,
  nickname VARCHAR(45) NOT NULL,
  Logros_id_logro INT NOT NULL,
  id_juego INT NOT NULL,
  Logros_nombre_juego VARCHAR(100) NOT NULL,
  nombre_logro VARCHAR(100) NOT NULL,
  fecha_obtencion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario_logro),
  INDEX (id_usuario, nickname),
  INDEX (Logros_id_logro, id_juego, Logros_nombre_juego, nombre_logro),
  FOREIGN KEY (id_usuario, nickname) REFERENCES Usuarios(id_usuario, nickname),
  FOREIGN KEY (Logros_id_logro, id_juego, Logros_nombre_juego, nombre_logro)
    REFERENCES Logros(id_logro, id_juego, nombre_juego, nombre_logro)
) ENGINE=InnoDB;

-- ----------------------------
-- Categorias
-- ----------------------------
CREATE TABLE Categorias (
  id_categoria INT NOT NULL AUTO_INCREMENT,
  categoria VARCHAR(45) NOT NULL,
  PRIMARY KEY (id_categoria, categoria),
  UNIQUE (categoria)
) ENGINE=InnoDB;

-- ----------------------------
-- Categorias_Juego
-- ----------------------------
CREATE TABLE Categorias_Juego (
  id_categoria INT NOT NULL,
  categoria VARCHAR(45) NOT NULL,
  id_juego INT NOT NULL,
  nombre_juego VARCHAR(100) NOT NULL,
  INDEX (id_categoria, categoria),
  INDEX (id_juego, nombre_juego),
  UNIQUE (id_juego),
  UNIQUE (id_categoria),
  FOREIGN KEY (id_categoria, categoria) REFERENCES Categorias(id_categoria, categoria),
  FOREIGN KEY (id_juego, nombre_juego) REFERENCES Juegos(id_juego, nombre_juego)
) ENGINE=InnoDB;

-- ----------------------------
-- Administradores
-- ----------------------------
CREATE TABLE Administadores (
  id_administador INT NOT NULL,
  id_usuario INT NOT NULL,
  nickname VARCHAR(45) NOT NULL,
  PRIMARY KEY (id_administador, id_usuario, nickname),
  INDEX (id_usuario, nickname),
  FOREIGN KEY (id_usuario, nickname) REFERENCES Usuarios(id_usuario, nickname)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;