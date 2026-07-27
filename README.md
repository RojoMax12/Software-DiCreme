# Software-DiCreme

Sistema web integral para la gestión de catálogo de productos, cotizaciones, pedidos, control de inventario por lotes, despachos y facturación de la empresa **DiCreme**.

---

## Arquitectura y Tecnologías

El proyecto está estructurado en una arquitectura cliente-servidor desacoplada:

- **Backend (`backend-dicreme`)**: API REST desarrollada con **PHP 8.3+** y **Laravel 11**, autenticación JWT (`jwt.auth`), control de acceso basado en roles (RBAC) y base de datos relacional **MySQL**.
- **Frontend (`frontend-dicreme`)**: Single Page Application (SPA) construida con **Vue 3 (Composition API)**, **TypeScript**, **Vite**, **Vue Router**, **Axios**, **jsPDF** (generación de PDFs de cotización) y **XLSX** (exportación a Excel).

---

## Roles del Sistema

El sistema implementa 4 roles de usuario diferenciados con permisos específicos:

1. **Rol 1 - Admin**: Acceso total a la plataforma, gestión de usuarios, cotizaciones, inventario, pedidos y reportes.
2. **Rol 2 - Trabajador**: Personal interno de DiCreme encargado del catálogo, control de stock, gestión de lotes y pedidos.
3. **Rol 3 - Distribuidor**: Clientes y empresas externas que exploran el catálogo, cotizan productos y realizan pedidos.
4. **Rol 4 - Despachador**: Personal logístico encargado del control de entregas, estado de despachos y recepciones.

---

## 🐳 Despliegue Rápido con Docker (Recomendado)

El proyecto incluye orquestación con **Docker Compose** para desplegar automáticamente la base de datos MySQL, la API Backend y la aplicación Frontend sin necesidad de instalar entornos manualmente.

### Pasos para desplegar con Docker:

```bash
# 1. Clonar el repositorio
git clone https://github.com/RojoMax12/Software-DiCreme.git
cd Software-DiCreme

# 2. Levantar todos los servicios en segundo plano
docker compose up -d --build
```

**Servicios levantados:**
- 🛢️ **Base de Datos PostgreSQL**: `localhost:5176`
- ⚡ **Backend REST API**: `http://localhost:8000`
- 💻 **Frontend SPA Vue 3**: `http://localhost:5173`

---

## ⚙️ Guía de Instalación y Despliegue Local (Sin Docker)

### 1. Prerrequisitos

- **Git**
- **PHP 8.3+**
- **Composer**
- **Node.js 20.19+**
- **npm** o **pnpm**
- Servidor de base de datos **MySQL 8.0**

### 2. Configuración y Arranque del Backend (`backend-dicreme`)

```bash
cd backend-dicreme

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env

# Generar claves de aplicación y JWT
php artisan key:generate
php artisan jwt:secret

# Enlace simbólico de almacenamiento
php artisan storage:link

# Ejecutar migraciones y datos iniciales
php artisan migrate --seed

# Levantar el servidor local
php artisan serve
```

### 3. Configuración y Arranque del Frontend (`frontend-dicreme`)

```bash
cd frontend-dicreme

# Instalar dependencias y levantar desarrollo
npm install
npm run dev
```

---

## 🧪 Pruebas Automatizadas (QA)

El backend cuenta con suites de pruebas unitarias y de integración desarrolladas con **PHPUnit**:

```bash
cd backend-dicreme

# Ejecutar la suite completa de pruebas
php artisan test
```

### Pruebas incluidas:
- `AuthTest.php`: Validación de inicio de sesión, generación de token JWT y protección de endpoints.
- `ProductoTest.php`: Validación de endpoints de catálogo, productos y resúmenes de stock.

---

## 📑 Documentación de la API (Postman)

La API REST cuenta con una colección oficial de Postman v2.1 con todos los endpoints catalogados por módulos:

- 📂 **Archivo de colección**: [`docs/DiCreme_API.postman_collection.json`](./docs/DiCreme_API.postman_collection.json)
- **Instrucciones de uso**:
  1. Abre Postman y haz clic en **Import**.
  2. Selecciona el archivo `docs/DiCreme_API.postman_collection.json`.
  3. Ejecuta el endpoint `Auth -> Login` para obtener automáticamente tu `access_token` JWT.

---

## 🛠️ Comandos Útiles de Desarrollo Local

### Backend (`backend-dicreme`)

- **Ejecutar migraciones**: `php artisan migrate`
- **Revertir migración**: `php artisan migrate:rollback`
- **Reiniciar base de datos completa con datos de prueba**: `php artisan migrate:fresh --seed`
- **Limpiar cachés**: `php artisan config:clear && php artisan cache:clear`

### Frontend (`frontend-dicreme`)

- **Servidor de desarrollo**: `npm run dev`
- **Comprobación de tipos TypeScript**: `npm run type-check`
- **Compilación de producción**: `npm run build`
