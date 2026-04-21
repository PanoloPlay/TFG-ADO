-- ----------------------------
-- Limpieza de datos previa
-- ----------------------------
SET FOREIGN_KEY_CHECKS = 0;
USE TFG_ADO_Tienda_Videojuegos;

DELETE FROM ListaDeseos;
DELETE FROM Administadores;
DELETE FROM Categorias_Juego;
DELETE FROM Categorias;
DELETE FROM LogrosUsuario;
DELETE FROM Biblioteca;
DELETE FROM Mensajes;
DELETE FROM Amigos;
DELETE FROM Logros;
DELETE FROM Valoraciones;
DELETE FROM IdiomasJuego;
DELETE FROM Juegos;
DELETE FROM Usuarios;
DELETE FROM Idiomas;

-- Opcional: Resetear contadores por si acaso
ALTER TABLE Usuarios AUTO_INCREMENT = 1;
ALTER TABLE Juegos AUTO_INCREMENT = 1;
ALTER TABLE Categorias AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- Inserciones de Idiomas
-- ----------------------------
INSERT INTO Idiomas (id_idioma, idioma) VALUES
('ES', 'Español'),
('EN', 'English'),
('FR', 'Français'),
('DE', 'Deutsch'),
('IT', 'Italiano'),
('AR', 'العربية');

-- ----------------------------
-- Inserciones de Usuarios (ID manual)
-- ----------------------------
INSERT INTO Usuarios (id_usuario, nombre_usuario, nickname, correo, clave_acceso, fecha_registro, descripcion, visibilidad, id_idioma_principal, id_idioma_secundario) VALUES
(1, 'Test', 'Test', 't@t.t', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', '2026-04-12 10:00:00', 'Usuario de prueba', 'publico', 'ES', 'EN'),
(2, 'Panolo', 'PanoloPlay', 'ahmad@gmail.com', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', '2026-04-12 10:00:00', 'Fanática de los videojuegos...', 'publico', 'ES', 'AR'),
(3, 'Praxis', 'Praxis99', 'david@gmail.com', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', '2026-04-12 10:30:00', '', 'solo_amigos', 'ES', NULL),
(4, 'Kans', 'Kans2950', 'oacar@gmail.com', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', '2026-04-12 11:00:00', 'Amante de los juegos retro', 'privado', 'ES', 'EN');

-- ----------------------------
-- Inserciones de Juegos (ID manual)
-- ----------------------------
INSERT INTO Juegos (id_juego, nombre_juego, descripcion, fecha_publicacion, desarrollador, precio, descuento) VALUES
(1, 'Undertale', 'Un RPG innovador...', '2015-09-15 00:00:00', 'Toby Fox', 9.99, NULL),
(2, 'Hollow Knight: Silksong', 'Secuela de Hollow Knight...', '2023-04-01 00:00:00', 'Team Cherry', 24.99, 10.00),
(3, 'Subnautica', 'Un juego de supervivencia...', '2018-01-23 00:00:00', 'Unknown Worlds Entertainment', 29.99, 15.00);

-- ----------------------------
-- Inserciones de IdiomasJuego (ID manual)
-- ----------------------------
INSERT INTO IdiomasJuego (id_idioma_juego, id_juego, nombre_juego, id_idioma) VALUES
(1, 1, 'Undertale', 'EN'),
(2, 2, 'Hollow Knight: Silksong', 'ES'),
(3, 2, 'Hollow Knight: Silksong', 'EN'),
(4, 3, 'Subnautica', 'ES'),
(5, 3, 'Subnautica', 'EN');

-- ----------------------------
-- Inserciones de Valoraciones (ID manual)
-- ----------------------------
INSERT INTO Valoraciones (id_valoracion, id_juego, nombre_juego, id_idioma_comentario, valoracion, comentario) VALUES
(1, 1, 'Undertale', 'ES', 'negativa', 'No esta en español, me cago en ti TOBIFOOOOOX'),
(2, 2, 'Hollow Knight: Silksong', 'EN', 'positiva', 'Increíble historia, gráficos impresionantes.'),
(3, 3, 'Subnautica', 'ES', 'positiva', 'Diversión pura, un clásico moderno.');

-- ----------------------------
-- Inserciones de Logros (ID manual)
-- ----------------------------
INSERT INTO Logros (id_logro, nombre_logro, descripcion_logro, id_juego, nombre_juego) VALUES
(1, 'Pacifista', 'Completa el juego sin matar a nadie.', 1, 'Undertale'),
(2, 'Genocida', 'Completa el juego matando a todos los enemigos.', 1, 'Undertale'),
(3, 'True Pacifist', 'Alcanza el final verdadero sin hacer daño a nadie.', 1, 'Undertale'),
(4, 'Explorador Audaz', 'Descubre todas las áreas del mundo en Silksong.', 2, 'Hollow Knight: Silksong'),
(5, 'Silksong Supremo', 'Derrota a todos los jefes del juego.', 2, 'Hollow Knight: Silksong'),
(6, 'Viajero del Reino', 'Recoge todos los recuerdos en Silksong.', 2, 'Hollow Knight: Silksong'),
(7, 'Superviviente', 'Sobrevive durante 100 días en el océano de Subnautica.', 3, 'Subnautica'),
(8, 'Explorador del Abismo', 'Explora el fondo más profundo del océano.', 3, 'Subnautica'),
(9, 'Titan de Acero', 'Construye un Cyclops y explora el océano en él.', 3, 'Subnautica');

-- ----------------------------
-- Inserciones de Amigos (ID manual)
-- ----------------------------
INSERT INTO Amigos (id_amistad, id_usuario1, nickname1, id_usuario2, nickname2, estado) VALUES
(1, 4, 'Kans2950', 2, 'PanoloPlay', 'aceptada'),
(2, 2, 'PanoloPlay', 3, 'Praxis99', 'pendiente');

-- ----------------------------
-- Inserciones de Mensajes (ID manual)
-- ----------------------------
INSERT INTO Mensajes (id_mensaje, id_amistad, mensaje, fecha_envio, leido, id_remitente, nickname_remitente, id_destinatario, nickname_destinatario) VALUES
(1, 1, 'Hola, ¿quieres jugar al Silksong esta tarde?', '2026-04-12 12:00:00', 'si', 4, 'Kans2950', 2, 'PanoloPlay'),
(2, 1, '¡Claro! Me encantaría. A las 6pm está bien.', '2026-04-12 12:30:00', 'no', 2, 'PanoloPlay', 4, 'Kans2950');

-- ----------------------------
-- Inserciones de Biblioteca (ID manual)
-- ----------------------------
INSERT INTO Biblioteca (id_Biblioteca, id_usuario, nickname, id_juego, nombre_juego) VALUES
(1, 1, 'Test', 3, 'Subnautica'),
(2, 2, 'PanoloPlay', 1, 'Undertale'),
(3, 2, 'PanoloPlay', 3, 'Subnautica'),
(4, 3, 'Praxis99', 2, 'Hollow Knight: Silksong'),
(5, 4, 'Kans2950', 2, 'Hollow Knight: Silksong');

-- ----------------------------
-- Inserciones de LogrosUsuario (ID manual)
-- ----------------------------
INSERT INTO LogrosUsuario (id_usuario_logro, id_usuario, nickname, Logros_id_logro, id_juego, Logros_nombre_juego, nombre_logro, fecha_obtencion) VALUES
(1, 2, 'PanoloPlay', 1, 1, 'Undertale', 'Pacifista', '2026-04-12 15:00:00'),
(2, 4, 'Kans2950', 4, 2, 'Hollow Knight: Silksong', 'Explorador Audaz', '2026-04-12 18:00:00'),
(3, 4, 'Kans2950', 5, 2, 'Hollow Knight: Silksong', 'Silksong Supremo', '2026-04-12 18:30:00'),
(4, 4, 'Kans2950', 6, 2, 'Hollow Knight: Silksong', 'Viajero del Reino', '2026-04-12 19:00:00'),
(5, 2, 'PanoloPlay', 7, 3, 'Subnautica', 'Superviviente', '2026-04-12 19:30:00');

-- ----------------------------
-- Inserciones de Categorias (ID manual)
-- ----------------------------
INSERT INTO Categorias (id_categoria, categoria) VALUES
(1, 'Acción'),
(2, 'Aventura'),
(3, 'RPG'),
(4, 'Plataformas'),
(5, 'Indie'),
(6, 'Metroidvania'),
(7, 'Supervivencia');

-- ----------------------------
-- Inserciones de Categorias_Juego
-- ----------------------------
INSERT INTO Categorias_Juego (id_categoria, categoria, id_juego, nombre_juego) VALUES
(5, 'Indie', 1, 'Undertale'),
(2, 'Aventura', 1, 'Undertale'),
(3, 'RPG', 1, 'Undertale'),
(1, 'Acción', 2, 'Hollow Knight: Silksong'),
(2, 'Aventura', 2, 'Hollow Knight: Silksong'),
(6, 'Metroidvania', 2, 'Hollow Knight: Silksong'),
(1, 'Acción', 3, 'Subnautica'),
(2, 'Aventura', 3, 'Subnautica'),
(7, 'Supervivencia', 3, 'Subnautica');

-- ----------------------------
-- Inserciones de Administradores
-- ----------------------------
INSERT INTO Administadores (id_administador, id_usuario, nickname) VALUES
(1, 2, 'PanoloPlay'),
(2, 3, 'Praxis99'),
(3, 4, 'Kans2950');

-- ----------------------------
-- Inserciones de ListaDeseos (ID manual)
-- ----------------------------
INSERT INTO ListaDeseos (id_Wishlist, id_usuario, nickname, id_juego, nombre_juego) VALUES
(1, 1, 'Test', 1, 'Undertale'),
(2, 1, 'Test', 2, 'Hollow Knight: Silksong'),
(3, 2, 'PanoloPlay', 2, 'Hollow Knight: Silksong'),
(4, 4, 'Kans2950', 1, 'Undertale'),
(5, 4, 'Kans2950', 2, 'Hollow Knight: Silksong');