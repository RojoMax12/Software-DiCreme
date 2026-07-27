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

## Prerrequisitos

Antes de iniciar, asegúrate de tener instalado en tu equipo:

- **Git**
- **PHP 8.3+**
- **Composer**
- **Node.js 20.19+** (o versión LTS compatible)
- **npm** o **pnpm**
- Servidor de base de datos **MySQL** (o PostgreSQL usando `DiCremeInventoryScript.sql`)

---

## Guía de Instalación y Despliegue Local

### 1. Clonar el repositorio

```bash
git clone https://github.com/RojoMax12/Software-DiCreme.git
cd Software-DiCreme
```

---

### 2. Configuración y Arranque del Backend (`backend-dicreme`)

1. Entrar al directorio del backend:
   ```bash
   cd backend-dicreme
   ```

2. Instalar dependencias de PHP:
   ```bash
   composer install
   ```

3. Instalar dependencias de Node (para desarrollo de assets):
   ```bash
   npm install
   ```

4. Configurar el archivo de entorno `.env`:
   - Copiar el archivo de ejemplo:
     ```bash
     cp .env.example .env
     ```
   - Configurar la conexión a tu base de datos MySQL (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
   - Configurar `APP_URL=http://127.0.0.1:8000`.

5. Generar la clave de la aplicación y la clave JWT:
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

6. Crear el enlace simbólico del almacenamiento público (para avatares y fotos de productos):
   ```bash
   php artisan storage:link
   ```

7. Ejecutar migraciones y datos iniciales:
   ```bash
   php artisan migrate --seed
   ```
   *(Opcional: Si deseas inicializar la base de datos desde el script SQL incluido, importa `DiCremeInventoryScript.sql` en tu gestor de base de datos).*

8. Iniciar el servidor local de desarrollo:
   ```bash
   php artisan serve
   ```
   El backend estará disponible en `http://127.0.0.1:8000`.

---

### 3. Configuración y Arranque del Frontend (`frontend-dicreme`)

1. Abre otra terminal y entra al directorio del frontend:
   ```bash
   cd frontend-dicreme
   ```

2. Instalar dependencias de paquetes:
   ```bash
   npm install
   ```

3. Iniciar el servidor de desarrollo Vite:
   ```bash
   npm run dev
   ```
   El frontend estará disponible en `http://localhost:5173`.

---

## Comandos Útiles

- **Limpiar cachés del backend**:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  ```

- **Reconstruir la base de datos con datos de prueba**:
  ```bash
  php artisan migrate:fresh --seed
  ```

- **Validación de tipos TypeScript en el frontend**:
  ```bash
  npm run type-check
  ```

- **Compilar el frontend para producción**:
  ```bash
  npm run build
  ```
