# 🎵 SoundVerse Streaming — Plataforma de Música

Sistema de streaming musical con modelo Freemium/Premium, desarrollado en PHP POO con PDO y MySQL.

## 📋 Requisitos

- **XAMPP** (PHP 7.4+ / 8.x, MySQL 5.7+ / MariaDB 10.3+)
- Navegador web moderno
- Contraseña de BD: **dejar en blanco** (root sin password — estándar XAMPP)

## 🚀 Instalación

### Opción 1: Importar SQL directamente

1. Iniciar Apache y MySQL desde XAMPP
2. Abrir **phpMyAdmin** (`http://localhost/phpmyadmin`)
3. Ir a la pestaña **Importar**
4. Seleccionar el archivo `lp3_streaming_musica.sql`
5. Ejecutar — se creará automáticamente la BD `lp3_streaming_musica` con todos los datos

### Opción 2: Desde línea de comandos

```bash
mysql -u root < lp3_streaming_musica.sql
```

## 🔑 Credenciales de prueba

### Panel Administrador
| Usuario | Email | Contraseña |
|---------|-------|------------|
| Admin | admin@soundverse.com | admin123 |

### Usuarios
| Tipo | Email | Contraseña |
|------|-------|------------|
| Free | usuario@correo.com | user123 |
| Premium | premium@correo.com | prem123 |

## 📁 Estructura del proyecto

```
soundverse-streaming/
├── classes/          # Clases PHP (POO estricta — 14 clases)
├── controllers/      # Documentación de arquitectura
├── admin/            # Panel de administración
│   ├── php/          # Controladores admin (queries.php)
│   └── vistas/       # Vistas admin
├── user/             # Panel de usuario
│   ├── php/          # Controladores user (queries.php)
│   └── vistas/       # Vistas user
├── js/               # JavaScript del frontend
├── assets/           # Recursos estáticos (imágenes, audio, música)
├── logs/             # Archivos de log (.log)
├── index.php         # Instalador y login principal
└── lp3_streaming_musica.sql  # Script completo de BD
```

## 🎯 Funcionalidades principales

- **Streaming musical** con reproductor completo (play, pause, skip, shuffle, repeat)
- **Modelo Freemium/Premium** con restricciones diferenciadas
  - Usuarios Free: publicidad cada 4-5 canciones, máximo 6 skips/hora, modo aleatorio obligatorio en playlists ajenas
  - Usuarios Premium: sin anuncios, skips ilimitados, control de calidad de audio
- **Playlists** personales, públicas y colaborativas (máx. 10,000 canciones)
- **Descubrimiento semanal** basado en géneros favoritos
- **Motor de recomendaciones** (radio por canción, artistas seguidos)
- **Seguimiento de artistas** con notificaciones de nuevo contenido
- **Estadísticas personales** con gráficas (Google Charts)
- **Historial de reproducción** (90 días Free / ilimitado Premium)
- **Anti-bots**: escucha < 10s descartada, > 5 reps/hora ignorada
- **Regalías**: $0.005 por reproducción válida (≥ 30 segundos)
- **Dashboard admin** con 12+ KPIs (DAU, MAU, retención, conversión, ingresos)
- **Soporte bilingüe** (ES/EN) con 150+ traducciones

## 📊 Base de datos

La BD incluye tablas normalizadas (3FN) con:
- `estado_disponible` en todas las tablas principales (borrado lógico)
- Sentencias preparadas PDO en todas las consultas (anti-SQL injection)
- Transacciones para operaciones críticas (upgrade, eliminación)

## 📝 Notas

- Los controladores están organizados por rol (`/admin/php/` y `/user/php/`) para separar contextos de seguridad
- Las vistas dentro de cada módulo (`/admin/vistas/` y `/user/vistas/`) tienen protección de sesión individual
- Logs de operaciones y errores se guardan en `/logs/` (no usa error_log nativo)
