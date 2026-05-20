-- ----------------------------
-- Limpieza de datos previa
-- ----------------------------
SET FOREIGN_KEY_CHECKS = 0;
USE TFG_ADO_Tienda_Hestias_Lotus;

DELETE FROM ListaDeseos;
DELETE FROM Administadores;
DELETE FROM CategoriasJuego;
DELETE FROM Categorias;
DELETE FROM LogrosUsuario;
DELETE FROM Biblioteca;
DELETE FROM Mensajes;
DELETE FROM Amigos;
DELETE FROM Logros;
DELETE FROM Valoraciones;
DELETE FROM IdiomasJuego;
DELETE FROM Juegos;
DELETE FROM Desarrollador;
DELETE FROM Usuarios;
DELETE FROM Idiomas;

-- Opcional: Resetear contadores por si acaso
ALTER TABLE Usuarios AUTO_INCREMENT = 1;
ALTER TABLE Juegos AUTO_INCREMENT = 1;
ALTER TABLE Categorias AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- 1. Idiomas
-- ----------------------------
INSERT INTO Idiomas (id_idioma, idioma) VALUES
('ES', 'Español'),
('EN', 'English'),
('FR', 'Français'),
('DE', 'Deutsch'),
('IT', 'Italiano'),
('PT', 'Português'),
('ZH', '中文 (Chinese)'),
('JA', '日本語 (Japanese)'),
('KO', '한국어 (Korean)'),
('RU', 'Русский (Russian)'),
('AR', 'العربية (Arabic)'),
('HI', 'हिन्दी (Hindi)'),
('BN', 'বাংলা (Bengali)'),
('PA', 'ਪੰਜਾਬੀ (Punjabi)'),
('JV', 'Jawa (Javanese)'),
('MS', 'Bahasa Melayu'),
('VI', 'Tiếng Việt'),
('TR', 'Türkçe'),
('PL', 'Polski'),
('NL', 'Nederlands'),
('EL', 'Ελληνικά (Greek)'),
('TH', 'ไทย (Thai)'),
('ID', 'Bahasa Indonesia'),
('FA', 'فارسی (Persian)'),
('UK', 'Українська (Ukrainian)'),
('RO', 'Română'),
('HU', 'Magyar'),
('CS', 'Čeština'),
('SV', 'Svenska'),
('FI', 'Suomi'),
('DA', 'Dansk'),
('NO', 'Norsk'),
('HE', 'עברית (Hebrew)'),
('TL', 'Tagalog'),
('SW', 'Kiswahili'),
('AM', 'አማርኛ (Amharic)'),
('YO', 'Yorùbá'),
('IG', 'Igbo'),
('UR', 'اردو (Urdu)'),
('CA', 'Català'),
('EU', 'Euskara'),
('GL', 'Galego');

-- ----------------------------
-- 2. Usuarios
-- ----------------------------
INSERT INTO Usuarios (id_usuario, nombre_usuario, nickname, correo, clave_acceso, fecha_registro, descripcion, visibilidad, id_idioma_principal, id_idioma_secundario, clave_amigos) VALUES
(1, 'Test User', 'Test', 't@t.t', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', NOW(), 'Usuario de prueba', 'publico', 'ES', 'EN', 123456789),
(2, 'Panolo', 'PanoloPlay', 'ahmad@gmail.com', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', NOW(), 'Fanático de los videojuegos...', 'publico', 'ES', 'AR', 783249012),
(3, 'David Praxis', 'Praxis99', 'david@gmail.com', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', NOW(), '', 'solo_amigos', 'ES', NULL, 345628578),
(4, 'Oscar Kans', 'Kans2950', 'oacar@gmail.com', '$2y$10$Wg/N0u/BNPaNIyGrIDB0jubnf6nAMP45Ujs5EPSeOSCpgyKUoWPKW', NOW(), 'Amante de los juegos retro', 'privado', 'ES', 'EN', 901746234);

-- ----------------------------
-- 3. Desarrolladores (Obligatorio antes que Juegos)
-- ----------------------------
INSERT INTO Desarrollador (id_desarrollador, nombre_desarrollador, nickname) VALUES
(1, 'Toby Fox', 'Test'),
(2, 'Team Cherry', 'Praxis99'),
(3, 'Unknown Worlds Entertainment', 'Kans2950');

-- ----------------------------
-- 4. Juegos
-- ----------------------------
INSERT INTO Juegos (id_juego, nombre_juego, descripcion, fecha_publicacion, desarrollador, precio, descuento) VALUES
(1, 'Undertale', 'Un RPG innovador...', '2015-09-15 00:00:00', 'Toby Fox', 9.99, 0.00),
(2, 'Hollow Knight: Silksong', 'Secuela de Hollow Knight...', '2023-04-01 00:00:00', 'Team Cherry', 24.99, 10.00),
(3, 'Subnautica', 'Un juego de supervivencia...', '2018-01-23 00:00:00', 'Unknown Worlds Entertainment', 29.99, 15.00);

-- ----------------------------
-- 5. IdiomasJuego
-- ----------------------------
INSERT INTO IdiomasJuego (id_juego, nombre_juego, id_idioma) VALUES
(1, 'Undertale', 'EN'),
(2, 'Hollow Knight: Silksong', 'ES'),
(2, 'Hollow Knight: Silksong', 'EN'),
(3, 'Subnautica', 'ES'),
(3, 'Subnautica', 'EN');

-- ----------------------------
-- 6. Valoraciones (Añadida fechaPublicacion)
-- ----------------------------
INSERT INTO Valoraciones (nombre_juego, nickname, id_idioma_comentario, valoracion, comentario, fechaPublicacion) VALUES
('Undertale', 'PanoloPlay', 'ES', 'negativa', 'No esta en español, me cago en ti TOBIFOOOOOX', NOW()),
('Hollow Knight: Silksong', 'PanoloPlay', 'EN', 'positiva', 'Increíble historia, gráficos impresionantes.', NOW()),
('Subnautica', 'PanoloPlay', 'ES', 'positiva', 'Diversión pura, un clásico moderno.', NOW());

-- ----------------------------
-- 7. Logros
-- ----------------------------
INSERT INTO Logros (id_logro, nombre_logro, descripcion_logro, id_juego, nombre_juego, rareza, identificador_unico) VALUES
(1, 'Pacifista', 'Completa el juego sin matar a nadie.', 1, 'Undertale', 'cobre', '1_1_cobre'),
(2, 'Genocida', 'Completa el juego matando a todos los enemigos.', 1, 'Undertale', 'plata', '2_1_plata'),
(4, 'Explorador Audaz', 'Descubre todas las áreas del mundo en Silksong.', 2, 'Hollow Knight: Silksong', 'oro', '4_2_oro'),
(7, 'Superviviente', 'Completa todos los logros del juego.', 3, 'Subnautica', 'lotus', '8_3_lotus'),
(8, 'Superviviente', 'Completa todos los logros del juego.', 3, 'Subnautica', 'lotus', '8_3_lotus'),
(9, 'Silksong Supremo', 'Derrota a todos los jefes del juego.', 2, 'Hollow Knight: Silksong', 'platino', '9_2_platino'),
(10, 'Superviviente', 'Sobrevive durante 100 días en el océano de Subnautica.', 3, 'Subnautica', 'platino', '10_3_platino'),
(11, 'Superviviente', 'Completa todos los logros del juego.', 3, 'Subnautica', 'lotus', '11_3_lotus');

-- ----------------------------
-- 8. Amigos
-- ----------------------------
INSERT INTO Amigos (id_usuario1, nickname1, id_usuario2, nickname2, estado) VALUES
(4, 'Kans2950', 2, 'PanoloPlay', 'aceptada'),
(2, 'PanoloPlay', 3, 'Praxis99', 'pendiente');

-- ----------------------------
-- 9. Mensajes
-- ----------------------------
INSERT INTO Mensajes (id_amistad, mensaje, fecha_envio, leido, id_remitente, nickname_remitente, id_destinatario, nickname_destinatario) VALUES
(1, 'Hola, ¿quieres jugar al Silksong esta tarde?', NOW(), 'si', 4, 'Kans2950', 2, 'PanoloPlay'),
(1, '¡Claro! Me encantaría.', NOW(), 'no', 2, 'PanoloPlay', 4, 'Kans2950');

-- ----------------------------
-- 10. Categorias
-- ----------------------------
INSERT INTO Categorias (id_categoria, categoria) VALUES
(1, 'Acción'),
(2, 'Aventura'),
(3, 'RPG'),
(4, 'Metroidvania'),
(5, 'Indie'),
(6, 'Supervivencia');

-- ----------------------------
-- 11. CategoriasJuego
-- ----------------------------
INSERT INTO CategoriasJuego (id_categoria, categoria, id_juego, nombre_juego) VALUES
(5, 'Indie', 1, 'Undertale'),
(3, 'RPG', 1, 'Undertale'),
(4, 'Metroidvania', 2, 'Hollow Knight: Silksong'),
(6, 'Supervivencia', 3, 'Subnautica');

-- ----------------------------
-- 12. Biblioteca y Lista de Deseos
-- ----------------------------
INSERT INTO Biblioteca (id_usuario, nickname, id_juego, nombre_juego) VALUES
(1, 'Test', 3, 'Subnautica'),
(2, 'PanoloPlay', 1, 'Undertale');

INSERT INTO ListaDeseos (id_usuario, nickname, id_juego, nombre_juego, numero_orden) VALUES
(1, 'Test', 1, 'Undertale', 1),
(1, 'Test', 2, 'Hollow Knight: Silksong', 2);

-- ----------------------------
-- 13. LogrosUsuario
-- ----------------------------
INSERT INTO LogrosUsuario (id_usuario, nickname, Logros_id_logro, id_juego, Logros_nombre_juego, nombre_logro, fecha_obtencion) VALUES
(2, 'PanoloPlay', 1, 1, 'Undertale', 'Pacifista', NOW());

-- ----------------------------
-- 14. Administradores
-- ----------------------------
INSERT INTO Administadores (id_administador, id_usuario, nickname) VALUES
(1, 2, 'PanoloPlay');

-- ----------------------------
-- 15. MultimediaJuego
-- ----------------------------
INSERT INTO MultimediaJuego
(id_multimedia, id_juego, nombre_juego, url_multimedia, tipo, numero_orden) VALUES
(1, 3, 'Subnautica', '../MEDIA/IMG/juegos/3/carusel/media_3_1779057902_80007a9c.jpg', 'imagen', 1),
(2, 3, 'Subnautica', '../MEDIA/IMG/juegos/3/carusel/media_3_1779057902_797864d4.jpg', 'imagen', 2),
(3, 3, 'Subnautica', '../MEDIA/IMG/juegos/3/carusel/media_3_1779057934_73567d32.jpg', 'imagen', 3),
(4, 3, 'Subnautica', '../MEDIA/IMG/juegos/3/carusel/media_3_1779057934_b5235eb7.jpg', 'imagen', 4),
(5, 3, 'Subnautica', '../MEDIA/IMG/juegos/3/carusel/media_3_1779057902_dbe0e704.jpg', 'imagen', 5);