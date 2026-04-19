# MusicApp – Android (Kotlin)
Cliente Android tipo Spotify para el backend **Soundwave**.

---

## 🗂 Estructura del proyecto

```
MusicApp/
├── app/src/main/
│   ├── java/com/musicapp/
│   │   ├── MusicApp.kt                  ← Application class
│   │   ├── data/
│   │   │   ├── SessionManager.kt        ← Sesión local (SharedPreferences)
│   │   │   ├── models/Models.kt         ← Data classes + UiState
│   │   │   └── network/
│   │   │       ├── ApiService.kt        ← Todos los endpoints (Retrofit)
│   │   │       ├── RetrofitClient.kt    ← Singleton OkHttp + Retrofit
│   │   │       └── PersistentCookieJar ← Mantiene PHPSESSID entre sesiones
│   │   ├── service/MusicService.kt      ← ExoPlayer en foreground
│   │   └── ui/
│   │       ├── auth/                    ← Login + Registro
│   │       ├── main/MainActivity        ← BottomNav + mini-player
│   │       ├── home/                    ← Biblioteca + búsqueda + filtro género
│   │       ├── player/                  ← Reproductor completo + PlayerViewModel
│   │       ├── playlists/               ← Lista, detalle, crear, eliminar
│   │       ├── recommend/               ← Recomendaciones por género (Last.fm)
│   │       └── admin/                   ← Stats + gestión de usuarios
│   └── res/
│       ├── layout/                      ← XMLs de pantallas e items
│       ├── navigation/nav_graph.xml     ← Grafo de navegación
│       ├── menu/                        ← BottomNav + toolbar
│       ├── drawable/                    ← Íconos vectoriales
│       ├── color/                       ← Selector nav activo
│       └── values/                      ← colors, themes, strings
```

---

## ⚙️ Configuración inicial

### 1. Cambiar la BASE_URL

Abre `RetrofitClient.kt` y ajusta la constante según tu entorno:

```kotlin
// Emulador Android Studio
const val BASE_URL = "http://10.0.2.2/soundwave/"

// Dispositivo físico en la misma red WiFi
const val BASE_URL = "http://192.168.1.XXX/soundwave/"   // IP de tu PC

// Producción (Hostinger u otro host)
const val BASE_URL = "https://tudominio.com/soundwave/"
```

### 2. Importar en Android Studio

1. **File → Open** → selecciona la carpeta `MusicApp/`
2. Espera a que Gradle sincronice (descargará dependencias ~2 min)
3. Si pide actualizar AGP, acepta la sugerencia

### 3. Requisitos del backend PHP

Asegúrate de que Apache/XAMPP esté corriendo y que:
- La carpeta del proyecto esté en `C:\xampp\htdocs\soundwave\`
- La BD `soundwave` exista en MySQL
- XAMPP tenga Apache + MySQL activos

### 4. Correr la app

- **Emulador:** crea un AVD con API 24+ y presiona ▶ Run
- **Dispositivo físico:** activa Depuración USB, conecta el cable

---

## 🔐 Flujo de autenticación

El backend usa **sesiones PHP (PHPSESSID)** en lugar de tokens JWT.
`PersistentCookieJar` guarda la cookie en `SharedPreferences` para
que la sesión sobreviva entre reinicios de la app sin volver a loguearse.

Como `isApiRequest()` detecta `action=` en POST, el login devuelve **JSON**:
- `{ "error": "..." }` → credenciales incorrectas
- Redirect a `home.php` / `admin.php` → login exitoso

---

## 🎵 Reproductor (ExoPlayer)

- `MusicService` corre en **foreground** con notificación persistente
- `PlayerViewModel` es **compartido** entre todos los fragmentos vía `activityViewModels()`
- El mini-player en `MainActivity` siempre muestra la canción actual
- Tap en el mini-player → abre `PlayerFragment` con controles completos
- Cada reproducción registra automáticamente un `history` en el backend

---

## 👑 Panel Admin

La tab **Admin** solo aparece si `session.isAdmin() == true`.
Desde ahí puedes ver estadísticas globales y gestionar usuarios
(cambiar rol user ↔ admin, eliminar).

---

## 📦 Dependencias principales

| Librería | Uso |
|---|---|
| Retrofit2 + Gson | Llamadas HTTP al backend PHP |
| OkHttp + Logging | Cookie jar + logs de red |
| ExoPlayer (Media3) | Reproducción de audio |
| Glide | Carga de imágenes / portadas |
| Navigation Component | Navegación entre fragmentos |
| ViewModel + LiveData | Estado reactivo de la UI |
| Material Components | Tema oscuro + componentes UI |

---

## 🚀 Pasos para subir a Hostinger (cuando esté listo)

1. Sube los archivos PHP a `public_html/soundwave/` via File Manager o FTP
2. Importa la BD en phpMyAdmin
3. En `RetrofitClient.kt` cambia `BASE_URL` a tu dominio HTTPS
4. Rebuild la app y distribuye el APK
