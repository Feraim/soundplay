# SoundPlay

##  Descripción
SoundPlay es una plataforma web que conecta artistas de música urbana con su audiencia local. Los artistas pueden subir canciones y gestionar su discografía; los oyentes descubren música por géneros como Trap, Rap, Drill, Techno y Reguetón.

---

##  Tecnologías

| Capa | Tecnología |
| :--- | :--- |
| **Backend** | PHP 8 · PDO · Patrón MVC |
| **Base de datos** | MySQL / MariaDB |
| **Frontend** | HTML5 · CSS3 · JavaScript vanilla |
| **Servidor** | XAMPP (Apache) |
| **Seguridad** | Bcrypt · CSRF tokens · RGPD |

---

##  Estructura del proyecto

```text
soundplay/
├── config/
│   └── conexion.php          # Conexión PDO (Singleton) + helpers CSRF
├── src/
│   ├── controlador/          # UsuarioController, CancionController, AjustesController
│   ├── modelo/               # Usuario, Artista, Cancion, Album, Transaccion, creditotecnico
│   └── vista/                # Vistas PHP por rol (admin, artista, user) + includes
├── assets/                   # CSS, JS e imágenes estáticas
├── uploads/                  # Archivos subidos (portadas y canciones)
├── tests/
│   ├── unitarias/            # Tests de validación y funcionalidad de modelos
│   └── integracion/          # Tests contra la BD real
├── index.php                 # Punto de entrada único (enrutador front-controller)
└── soundplay.sql             # Esquema de la base de datos
