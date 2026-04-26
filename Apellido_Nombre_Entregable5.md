# Entregable 5 - Desarrollo Avanzado y Despliegue (Borrador para Word)

*Aviso: Este es un documento preformateado. Por favor, copia su contenido, inclúyelo en tu archivo de Word (`Apellido_Nombre_Entregable5.docx`), edita tus datos y guárdalo exportado como Word o PDF para la entrega.*

---

## Portada
**Nombre del Estudiante:** [Tu Nombre completo]
**Módulo:** [Nombre del Módulo o Materia]
**Proyecto:** SoundPlay - Curando la Escena Local
**Fecha:** [Fecha actual]

## Índice
1. Descripción del Desarrollo Avanzado
   1.1. Backend
   1.2. Frontend
2. Explicación de Reglas de Negocio, Validaciones y Control de Errores
3. Diagrama de Despliegue Explicado
4. Registro de Incidencias
5. Enlaces de Entrega (Git y Aplicación)

---

## 1. Descripción del Desarrollo Avanzado

### 1.1 Backend
En el servidor de **SoundPlay** se ha consolidado la arquitectura mediante la implementación del patrón **MVC (Modelo-Vista-Controlador)**. Se desarrollaron las siguientes funcionalidades principales:
- **Gestor de Variables de Entorno (.env):** Se codificó un intérprete para leer y cargar variables del archivo `.env`, permitiendo separar las creedenciales de desarrollo y las de producción, lo que mejora la seguridad.
- **CRUD de Canciones y Base de Datos:** Se implementó una conexión estable con PDO hacia la base de datos `soundplay_db`, soportando operaciones de guardado de nuevos tracks directamente desde el Dashboard del Artista.
- **API Endpoints (Artistas y Canciones):** Se crearon controladores (`ArtistaController.php` y `CancionController.php`) que responden peticiones vía JSON para integrarlas con el frontend dinámicamente.

### 1.2 Frontend
- **Navegación Dinámica SPA-like:** El archivo `index.php` actúa como un *Front Controller* que carga vistas (`inicio.php`, `buscar.php`, `biblioteca.php`, `panel.php`) dependiendo del parámetro en la URL, todo estructurado sin recargar el "reproductor principal" que se mantiene estático en el footer.
- **Validación del lado del cliente y Servidor (Formularios):** Desarrollamos el panel de control del artista (`panel.php`) que valida en tiempo real, antes de hacer `submit`, verificando las extensiones permitidas (mp3, wav) y el tamaño límite (simulado y real en PHP).
- **Asincronismo:** Uso de `fetch` para consumir la base de datos de artistas cercanos, integrando la UI para mejorar el *feedback* hacia el usuario y gestionando estados de "Cargando" y "Subida exitosa" con notificaciones integradas.

---

## 2. Explicación de Reglas de Negocio, Validaciones y Control de Errores

### Reglas de Negocio
- **Límite de Tamano de Canción:** Ningún Master o track subido por el artista puede superar los `50MB` reales en la plataforma (`524288000 bytes` se usa como tope de sistema interno) para evitar saturación de almacenamiento.
- **Formatos Exclusivos:** Sólo se permite subir archivos de formato `MPEG Audio (mp3)` o `Waveform (wav)`.
- **Ruta de Carga Unificada:** Todos los archivos de audio se consolidan dentro del directorio `/uploads/` protegidos para el streaming en el reproductor web.

### Validaciones (Cliente y Servidor)
- **Frontend (JS):** Al enviar la canción en `panel.php`, se valida que exista un archivo seleccionado con los `MIME Types` correctos y con tamaño inferio al límite estipulado. Se evalúa el título para que tenga al menos 3 caracteres. Todo esto para prevenir llamadas HTTP innecesarias que deriven en sobrecarga del servidor.
- **Backend (PHP):** La validación más crítica tiene lugar en `CancionController.php`. Se verifica el array `$_FILES` buscando el índice de error, restringiendo tipos válidos con `in_array` y moviendo con validación de destino el archivo en `move_uploaded_file`.

### Control de Errores
- Toda interacción base de datos en PDO usa transacciones y captura excepciones (`try-catch`), regresando `JSON` con status `500` y descripciones acotadas que no expongan estructura del lado del DB para no comprometer el servidor en producción.
- Frontend maneja los errores decodificando el campo `error` de los endpoints y mostrándolos en color rojo (indicadores visuales) dentro del formulario interactivo, garantizando la experiencia del usuario (UX).

---

## 3. Diagrama de Despliegue Explicado

*(Nota para el estudiante: Inserta aquí o en el Word final el diagrama o pantallazo del esquema de despliegue si corresponde. A continuación el texto que lo describe).*

El diagrama de despliegue se divide en los siguientes nodos principales:
1. **Cliente Básico (Web Browser / Dispositivos Móviles):** Interactúa mediante protocolo seguro `HTTPS` enviando parámetros tipo POST, GET al servidor.
2. **Servidor de Aplicaciones (Linux Apache / Nginx):** Recibe las solicitudes en su raíz virtual (htdocs/public), ejecuta `PHP 8.x` para parsear `.env`, gestionar rutas, controladores, validaciones y responder.
3. **Servidor de Base de Datos (MySQL):** Escucha en puerto privado (usualmente `3306`) y gestiona de manera relacional todas las entidades de Usuarios, Artistas, y Canciones. Se conecta al Servidor de Aplicaciones y procesa todas las transacciones generadas. 
Todo el tráfico externo es filtrado con el fin de proteger las operaciones del modelo CRUD.

---

## 4. Registro de Incidencias

A continuación se adjunta la tabla documentando los principales problemas presentados durante el desarrollo o simulación de despliegue:

| Fecha Incidencia | Causa | Solución Aplicada |
| :--- | :--- | :--- |
| [Escribe Fecha] | Error 500 al guardar un usuario. | Credenciales de base de datos incorrectas tras migrar a producción. Se procedió a actualización de credenciales. |
| [Escribe Fecha] | Variables de entorno no cargadas en producción. | Archivo `.env` no incluido en el despliegue del servidor. Configuración manual usando la clase `EnvLoader`. |
| [Escribe Fecha] | Falla de Carga AJAX/Fetch del controlador. | El sistema buscaba el script en `/assets/js/main.js` pero residía en `/assets/main.js`. Re-direccionamiento en código fuente. |
| [Escribe Fecha] | Problema de Rutas SPA fallida en Front Controller | Al querer mostrar un tab diferente no encontraba el destino. Se armó lógica de ruteo `$page` admitiendo una lista en un in_array de seguridad. |

---

## 5. Accesos de Entrega

- **Repositorio Git:** [Pega acá el enlace a tu repositorio público o privado en GitHub/GitLab]
- **Aplicación Desplegada (Producción):** [Pega acá la IP u hostname ej: https://miproyecto-deploy.railway.app]

`[Fin del Documento]`
