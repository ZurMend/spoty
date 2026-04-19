# 🎵 Soundwave — Guía de instalación

Clon de Spotify con PHP puro + MySQL + Last.fm API.  
**Sin frameworks, sin composer, sin npm.** Solo copia y funciona.

---

## Requisitos

| Herramienta | Versión mínima |
|-------------|---------------|
| PHP         | 8.0+          |
| MySQL       | 5.7+ / 8.x    |
| Servidor    | Apache + mod_rewrite  **o**  XAMPP/WAMP/Laragon |

---

## Pasos de instalación

### 1. Copiar los archivos

Coloca la carpeta `soundwave/` dentro del directorio raíz de tu servidor:

- **XAMPP (Windows/Mac):** `C:/xampp/htdocs/soundwave/`
- **WAMP (Windows):**      `C:/wamp64/www/soundwave/`
- **Laragon (Windows):**   `C:/laragon/www/soundwave/`
- **Linux/Mac manual:**    `/var/www/html/soundwave/`

### 2. Crear la base de datos

1. Abre **phpMyAdmin** (generalmente en `http://localhost/phpmyadmin`)
2. Haz clic en **"Importar"** (o "Nueva" → ejecutar SQL)
3. Selecciona el archivo: `soundwave/sql/soundwave.sql`
4. Haz clic en **"Continuar"** o **"Ejecutar"**

### 3. Configurar la conexión

Abre el archivo `soundwave/backend/config.php` y edita:

```php
define('DB_HOST',  'localhost');   // normalmente localhost
define('DB_NAME',  'soundwave');   // nombre de la BD que creaste
define('DB_USER',  'root');        // tu usuario MySQL
define('DB_PASS',  '');            // tu contraseña MySQL (vacía en XAMPP por defecto)
define('BASE_URL', 'http://localhost/soundwave');  // URL base de tu proyecto
```

### 4. Obtener API Key de Last.fm (GRATIS, sin tarjeta)

1. Ve a: https://www.last.fm/join (crear cuenta gratuita)
2. Luego ve a: https://www.last.fm/api/account/create
3. Llena el formulario:
   - **Application name:** Soundwave
   - **Application description:** Music player app
   - **Callback URL:** puedes dejarlo vacío
4. Copia tu **API Key** (cadena de 32 caracteres)
5. Pégala en `config.php`:
```php
define('LASTFM_API_KEY', 'tu_api_key_de_32_caracteres_aqui');
```

### 5. Permisos de carpetas (Linux/Mac)

```bash
chmod 755 soundwave/uploads/songs/
chmod 755 soundwave/uploads/covers/
```

En Windows con XAMPP/WAMP no necesitas hacer esto.

### 6. Abrir el navegador

Ve a: **http://localhost/soundwave**

Te redirigirá al login. Crea tu cuenta y empieza.

---

## Estructura de archivos

```
soundwave/
├── index.php                    ← Entrada: redirige a login o home
├── sql/
│   └── soundwave.sql            ← Ejecutar en phpMyAdmin
├── backend/
│   ├── config.php               ← ⚙️ EDITAR ESTE PRIMERO
│   ├── auth.php                 ← Login / Registro / Logout
│   ├── songs.php                ← Listar / Subir / Eliminar canciones
│   ├── playlists.php            ← CRUD de playlists
│   └── recommend.php            ← Recomendaciones via Last.fm
├── frontend/
│   ├── css/
│   │   └── style.css            ← Todos los estilos
│   ├── js/
│   │   └── app.js               ← Reproductor + llamadas API
│   └── pages/
│       ├── login.php            ← Pantalla de inicio de sesión
│       ├── register.php         ← Pantalla de registro
│       ├── home.php             ← Lista de canciones + reproductor
│       ├── playlists.php        ← Mis playlists
│       └── upload.php           ← Subir nueva canción
└── uploads/
    ├── songs/                   ← Archivos de audio (mp3, wav…)
    └── covers/                  ← Imágenes de portadas
```

---

## Funcionalidades incluidas

- ✅ Registro e inicio de sesión con contraseñas cifradas (bcrypt)
- ✅ Biblioteca de canciones con búsqueda y filtro por género
- ✅ Reproductor completo: play/pause, anterior/siguiente, shuffle, repeat
- ✅ Barra de progreso interactiva y control de volumen
- ✅ Subida de canciones con drag & drop y progreso visual
- ✅ Recomendaciones automáticas por género vía Last.fm (al reproducir)
- ✅ Crear playlists, agregar/quitar canciones
- ✅ Diseño oscuro tipo Spotify, totalmente responsive

---

## Solución de problemas frecuentes

| Problema | Solución |
|----------|---------|
| Página en blanco | Verifica que PHP esté activo y la URL en config.php sea correcta |
| Error de BD | Verifica usuario/contraseña MySQL en config.php |
| No sube archivos | Verifica `upload_max_filesize` en php.ini (mínimo 50M) |
| No aparecen recomendaciones | Verifica que la API key de Last.fm sea correcta |
| Imágenes no cargan | Verifica que la carpeta uploads/ tenga permisos de escritura |

---

## Ajustar límite de subida en php.ini

Si los archivos no suben, edita `php.ini` (búscalo en XAMPP → Config → php.ini):

```ini
upload_max_filesize = 50M
post_max_size = 55M
max_execution_time = 120
```

Reinicia Apache después de cambiar esto.
