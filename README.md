# 
# 🎮 Hestia's Lotus

![Banner o logo del proyecto](https://github.com/PanoloPlay/TFG-ADO/blob/main/MEDIA/IMG/app_icons/loto-color.svg)

> Una plataforma web que ofrece la calidez que da la diosa del hogar Hestia.

## 🚀 Acceso a la web en AWS

[Web TFG-ADO Hestia's Lotus](http://tfg-ado-hestias-lotus.us-east-1.elasticbeanstalk.com/)


---

## 📖 Sobre el Proyecto

 Hestia's Lotus es una aplicación web diseñada para replicar la experiencia de una tienda de videojuegos digital que ofrezca la calidez que da la diosa del hogar Hestia. Los usuarios pueden navegar por un amplio catálogo, filtrar por géneros, ver los detalles de cada título y gestionar un carrito de compras, ademas de agregar y chatear con amigos.

Los usuarios pueden:

- Registrarse, iniciar sesión y cerrar sesión.
- Buscar y explorar juegos con distintos filtros.
- Ver páginas de detalle de cada juego con información, precio, descuentos, reseñas y recursos multimedia.
- Añadir juegos a la lista de deseos y gestionarla con orden personalizado.
- Añadir juegos al carrito y comprar productos individuales o todo el carrito.
- Acceder a una biblioteca personal de juegos ya comprados.
- Conectarse con otros jugadores mediante solicitudes de amistad y ver perfiles públicos/privados.
- Personalizar perfil y avatar.

## 🗂️ Estructura principal del proyecto

- `MAIN/` - Páginas públicas y de usuario.
- `AUTH/` - Autenticación: login, registro y cierre de sesión.
- `BBDD/` - Consultas SQL y conexión a la base de datos.
- `AJAX/` - APIs internas para peticiones dinámicas.
- `CSS/` - Estilos de la aplicación.
- `JS/` - Lógica del cliente y comportamientos interactivos.
- `MEDIA/` - Recursos multimedia: imágenes y vídeos de juegos.
- `DEPENDENCIES/` - Helpers reutilizables para imágenes, vídeos, sesiones y revisión.
- `GENERAL/` - Layout global y estructura común de HTML.
- `index.html` - Redirige automáticamente a `MAIN/example.php`.

---

## 🛠️ Tecnologías Utilizadas

| Frontend | Backend | Base de Datos | Otros |
| :--- | :--- | :--- | :--- |
| CSS(3) / Bootstrapv(5.3) | PHP(8.0) / JS(2025) | SQL(10.4.32-MariaDB) | HTML(5) |

---


## ⚙️ Instalación y uso

1. clonate o descargate el porllecto y ponlo en un servidor Web con PHP:

```bash
git clone https://github.com/PanoloPlay/TFG-ADO.git
```

2. Crea la base de datos en MariaDB/MySQL e importa los esquemas:

- `BBDD/SQL/schema.sql`
- `BBDD/SQL/test-data.sql`

3. Ajusta la conexión a la base de datos en `BBDD/conexion.php` si es necesario (usuario, contraseña y nombre de base de datos).

4. Accede a la aplicación en el navegador:


> Nota: `index.html` redirige automáticamente a `MAIN/example.php`.

---

## 📄 Licencia

Revisa `LICENSE.md` para conocer los términos de uso del proyecto.
