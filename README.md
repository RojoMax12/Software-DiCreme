# Software-DiCreme

Guía rápida para levantar el proyecto en local.

## Prerrequisitos

Antes de empezar, instala estas herramientas en tu equipo:

- Git
- PHP 8.3 o superior
- Composer
- Node.js 20.19+ o una versión más reciente compatible con el proyecto
- npm
- Un motor de base de datos local compatible con tu archivo `.env`

El backend incluye un script SQL llamado `DiCremeInventoryScript.sql` con sintaxis de PostgreSQL, así que si vas a usar ese archivo para inicializar la base, instala PostgreSQL.

## 1. Clonar y abrir el proyecto

Abre la carpeta raíz del repositorio:

```bash
cd /Software-DiCreme
```

## 2. Levantar el backend

### 2.1 Entrar al proyecto Laravel

```bash
cd backend-dicreme
```

### 2.2 Instalar dependencias PHP

```bash
composer install
```

### 2.3 Instalar dependencias de frontend del backend

El backend también usa Vite para sus assets, así que instala sus dependencias de Node:

```bash
npm install
```

### 2.4 Revisar el archivo de entorno

Verifica el archivo `.env` del backend y ajusta, si hace falta, estos datos:

- Conexión de base de datos
- `APP_URL`
- `JWT_SECRET`

Si tu instalación no tiene el archivo `.env`, crea uno con la configuración que use tu entorno local.

### 2.5 Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 2.6 Crear la base de datos y migrar

Primero crea la base de datos local indicada en `.env` y luego ejecuta:

```bash
php artisan migrate
```

Si necesitas cargar la estructura inicial desde el script incluido, importa `DiCremeInventoryScript.sql` en PostgreSQL antes o en lugar de las migraciones, según cómo quieras inicializar el entorno.

### 2.7 Levantar el backend

Para correr solo la API / servidor Laravel:

```bash
php artisan serve
```

Normalmente quedará disponible en `http://127.0.0.1:8000`.

Si además quieres levantar los assets del backend en modo desarrollo, abre otra terminal y ejecuta:

```bash
npm run dev
```

## 3. Levantar el frontend

### 3.1 Entrar al proyecto Vue

Abre otra terminal y entra al frontend:

```bash
cd /Users/fescobedo2025/Documents/GitHub/Software-DiCreme/frontend-dicreme
```

### 3.2 Instalar dependencias

```bash
npm install
```

### 3.3 Ejecutar el frontend en desarrollo

```bash
npm run dev
```

Vite normalmente lo levanta en `http://localhost:5173`.

## 4. Orden recomendado de arranque

1. Instalar prerrequisitos.
2. Configurar la base de datos local del backend.
3. Ejecutar `composer install` y `npm install` en `backend-dicreme`.
4. Ejecutar `php artisan key:generate` y `php artisan migrate`.
5. Levantar el backend con `php artisan serve`.
6. Levantar el frontend con `npm run dev` en `frontend-dicreme`.

## 5. Comandos útiles

- Limpiar cachés del backend:

```bash
php artisan config:clear
php artisan cache:clear
```

- Revertir la última migración:

```bash
php artisan migrate:rollback
```

- Reiniciar todo con datos de prueba:

```bash
php artisan migrate:fresh --seed
```
