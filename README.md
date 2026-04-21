# 🛍️ JKD Clothing — E-Commerce Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-00000F?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Docker-2CA5E0?style=for-the-badge&logo=docker&logoColor=white" />
  <img src="https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white" />
  <img src="https://img.shields.io/badge/JWT-black?style=for-the-badge&logo=JSON%20web%20tokens" />
</p>

> Plataforma de comercio electrónico para ropa y calzado, construida con **arquitectura de microservicios**, **Docker** y **Laravel**. Diseñada para ofrecer alta disponibilidad, escalabilidad y una experiencia de compra rápida y segura.

---

## 📋 Tabla de Contenidos

- [Descripción del Proyecto](#-descripción-del-proyecto)
- [Arquitectura del Sistema](#-arquitectura-del-sistema)
- [Servicios](#-servicios)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación y Configuración](#-instalación-y-configuración)
- [Variables de Entorno](#-variables-de-entorno)
- [Dockerfile](#-dockerfile)
- [Endpoints de la API](#-endpoints-de-la-api)
- [Esquema de Base de Datos](#-esquema-de-base-de-datos)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Puertos y Accesos](#-puertos-y-accesos)
- [Usuarios del Sistema](#-usuarios-del-sistema)
- [Riesgos y Consideraciones](#️-riesgos-y-consideraciones)
- [Contribuciones](#-contribuciones)

---

## 📌 Descripción del Proyecto

**JKD Clothing** es una plataforma de e-commerce que resuelve el problema de vender ropa y calzado de forma rápida, segura y sin interrupciones. Gracias a su arquitectura distribuida de microservicios, permite que la tienda esté siempre disponible, sea fácil de escalar cuando crecen las visitas y ofrezca una mejor experiencia de compra para los clientes.

**Funcionalidades principales:**

- Registro e inicio de sesión de usuarios con autenticación JWT
- Visualización del catálogo de ropa y calzado con categorías, marcas y atributos
- Variantes de productos (talla, color, etc.) con control de inventario por variante
- Gestión de imágenes por producto y por variante con orden y flag de imagen principal
- Panel de administración para gestión de inventario, precios y productos
- Gestión de roles y permisos granulares por módulo del sistema
- API Gateway centralizado con rate limiting, CORS y logging

---

## 🏗️ Arquitectura del Sistema

JKD Clothing utiliza una **arquitectura cliente-servidor distribuida** basada en microservicios. Cada servicio es independiente, se comunica mediante APIs REST (HTTP) y cuenta con su propia base de datos MySQL aislada. El **API Gateway** actúa como punto de entrada único y aplica middlewares transversales (autenticación, CORS, rate limiting, logging) antes de enrutar las peticiones al servicio correspondiente.

```
┌──────────────────────────────────────────────────────────────────┐
│                          CLIENTE                                  │
│                 (Navegador / App móvil)                           │
│            CORS: localhost:3000 | localhost:5173                  │
└────────────────────────┬─────────────────────────────────────────┘
                         │ HTTP :8080
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│                       API GATEWAY                                 │
│                   jd_gateway — Puerto 8080                        │
│                                                                   │
│  Middlewares: cors · rate.limit (60 req/min) · log.requests       │
│               auth.gateway (JWT validation)                       │
│                                                                   │
│  Rutas públicas:  POST /login · POST /register · GET /health      │
│  Rutas privadas:  /users/** · /permissions/** · /catalog/**       │
└───────────┬──────────────────────────────┬───────────────────────┘
            │ HTTP → jd_user_app:80        │ HTTP → jd_catalog_app:80
            │ timeout: 10s                 │ timeout: 10s
            ▼                             ▼
┌───────────────────────┐     ┌───────────────────────────────────┐
│     USER SERVICE      │     │         CATALOG SERVICE           │
│     jd_user_app       │     │         jd_catalog_app            │
│     Puerto 8000       │     │         Puerto 8001               │
│                       │     │                                   │
│  Auth · Users         │     │  Products · Categories · Brands   │
│  Roles · Permissions  │     │  Attributes · Variants · Images   │
│  Modules              │     │  Statuses                         │
└──────────┬────────────┘     └──────────────────┬────────────────┘
           │                                     │
           ▼                                     ▼
┌─────────────────────┐              ┌───────────────────────────┐
│      user_db        │              │        catalog_db         │
│   MySQL 8 — :3308   │              │     MySQL 8 — :3307       │
│                     │              │                           │
│  users              │              │  products                 │
│  user_details       │              │  product_variants         │
│  roles              │              │  product_images           │
│  permissions        │              │  product_statuses         │
│  modules            │              │  categories               │
│  role_module_perms  │              │  brands                   │
└─────────────────────┘              │  attributes               │
                                     │  attribute_values         │
                                     │  variant_attribute_values │
                                     └───────────────────────────┘
```

---

## ⚙️ Servicios

| Servicio | Contenedor | Puerto externo | Puerto interno | Descripción |
|---|---|---|---|---|
| API Gateway | `jd_gateway` | `8080` | `80` | Punto de entrada único. Enruta, autentica y aplica rate limiting |
| User Service | `jd_user_app` | `8000` | `80` | Autenticación JWT, usuarios, roles y permisos |
| Catalog Service | `jd_catalog_app` | `8001` | `80` | Productos, categorías, marcas, atributos y variantes |
| User DB | `jd_user_db` | `3308` | `3306` | Base de datos MySQL del servicio de usuarios |
| Catalog DB | `jd_catalog_db` | `3307` | `3306` | Base de datos MySQL del catálogo |
| phpMyAdmin | `jd_phpmyadmin` | `8081` | `80` | Interfaz web de administración de ambas bases de datos |

Todos los servicios se comunican internamente a través de la red Docker `jd_network` (bridge).

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Versión | Uso |
|---|---|---|
| **PHP** | 8.2 | Lenguaje backend de todos los microservicios |
| **Laravel** | 10.x | Framework PHP para los microservicios |
| **MySQL** | 8.0 | Base de datos relacional (una instancia por servicio) |
| **Docker** | >= 24.x | Contenerización de servicios |
| **Docker Compose** | >= 2.x | Orquestación local de todos los contenedores |
| **Apache** | 2.4 | Servidor web dentro de cada contenedor |
| **Composer** | 2.7 | Gestor de dependencias PHP |
| **JWT** | — | Autenticación sin estado entre cliente, gateway y servicios |
| **phpMyAdmin** | latest | Gestión visual de bases de datos |

---

## 📦 Requisitos Previos

Asegúrate de tener instalados los siguientes programas antes de comenzar:

| Herramienta | Versión mínima | Verificar instalación |
|---|---|---|
| [Docker](https://www.docker.com/get-started) | 24.x | `docker --version` |
| [Docker Compose](https://docs.docker.com/compose/) | 2.x | `docker compose version` |
| [Git](https://git-scm.com/) | 2.x | `git --version` |

> ✅ **No necesitas** instalar PHP, Composer ni MySQL en tu máquina local. Todo corre dentro de los contenedores Docker.

---

## 🚀 Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone https://github.com/bermudezkenny03/JKD-Clothing.git
cd JKD-Clothing
```

### 2. Configurar las variables de entorno

Copia los archivos `.env.example` para cada servicio y edítalos con tus valores:

```bash
# Gateway
cp gateway/.env.example gateway/.env

# User Service
cp user-service/.env.example user-service/.env

# Catalog Service
cp catalog-service/.env.example catalog-service/.env
```

> Consulta la sección [Variables de Entorno](#-variables-de-entorno) para ver todos los valores disponibles.

### 3. Construir y levantar los contenedores

```bash
docker compose up -d --build
```

Esto construirá las imágenes de PHP/Apache para cada servicio e iniciará todos los contenedores en segundo plano.

### 4. Verificar que los contenedores estén corriendo

```bash
docker compose ps
```

Deberías ver todos los contenedores con estado `Up`:

```
NAME               STATUS          PORTS
jd_gateway         Up              0.0.0.0:8080->80/tcp
jd_user_app        Up              0.0.0.0:8000->80/tcp
jd_catalog_app     Up              0.0.0.0:8001->80/tcp
jd_user_db         Up              0.0.0.0:3308->3306/tcp
jd_catalog_db      Up              0.0.0.0:3307->3306/tcp
jd_phpmyadmin      Up              0.0.0.0:8081->80/tcp
```

### 5. Instalar dependencias PHP (si los volúmenes están vacíos)

```bash
docker exec -it jd_user_app composer install
docker exec -it jd_catalog_app composer install
```

### 6. Generar las claves de la aplicación

```bash
docker exec -it jd_user_app php artisan key:generate
docker exec -it jd_catalog_app php artisan key:generate
```

### 7. Ejecutar migraciones y seeders

```bash
# User Service
docker exec -it jd_user_app php artisan migrate --seed

# Catalog Service
docker exec -it jd_catalog_app php artisan migrate --seed
```

### 8. Limpiar caché de configuración (recomendado tras cambios en `.env`)

```bash
docker exec -it jd_user_app php artisan config:clear && \
docker exec -it jd_user_app php artisan cache:clear

docker exec -it jd_catalog_app php artisan config:clear && \
docker exec -it jd_catalog_app php artisan cache:clear
```

### 9. Verificar el estado del Gateway

```bash
curl http://localhost:8080/health
```

Respuesta esperada:

```json
{
  "gateway": "ok",
  "version": "1.0",
  "timestamp": "2025-01-01T00:00:00+00:00"
}
```

---

### 🛑 Detener los servicios

```bash
docker compose down
```

Para detener **y eliminar los volúmenes** (⚠️ esto borra todas las bases de datos):

```bash
docker compose down -v
```

---

### 🔄 Reiniciar un servicio individual

```bash
docker compose restart gateway
docker compose restart user-service
docker compose restart catalog-service
```

---

### 📋 Ver logs de los servicios

```bash
# Todos los servicios en tiempo real
docker compose logs -f

# Un servicio específico
docker compose logs -f gateway
docker compose logs -f user-service
docker compose logs -f catalog-service
```

---

### 🐚 Acceder al shell de un contenedor

```bash
docker exec -it jd_user_app bash
docker exec -it jd_catalog_app bash
docker exec -it jd_gateway bash
```

---

## 🔑 Variables de Entorno

### Gateway (`gateway/.env`)

```env
APP_NAME=JDK-Gateway
APP_ENV=local
APP_KEY=                          # Generar con: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8080

# URLs internas de los microservicios (nombres de contenedor Docker)
USER_SERVICE_URL=http://jd_user_app:80
CATALOG_SERVICE_URL=http://jd_catalog_app:80

# JWT — debe ser igual en todos los servicios. Generar con: openssl rand -base64 64
JWT_SECRET=

# Rate limiting (peticiones por minuto por IP)
GATEWAY_RATE_LIMIT=60

# Timeouts en segundos por servicio
USER_SERVICE_TIMEOUT=10
CATALOG_SERVICE_TIMEOUT=10

# CORS — orígenes permitidos (ajustar según el dominio del frontend)
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173

LOG_CHANNEL=stack
LOG_LEVEL=debug
CACHE_STORE=file
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

### User Service (`user-service/.env`)

```env
APP_NAME=JD_USER_SERVICE
APP_ENV=local
APP_KEY=                          # Generar con: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos (nombre del contenedor Docker como host)
DB_CONNECTION=mysql
DB_HOST=user-db
DB_PORT=3306
DB_DATABASE=user_db
DB_USERNAME=                      # Definir en docker-compose.yml
DB_PASSWORD=                      # Definir en docker-compose.yml

# JWT — debe ser igual en todos los servicios. Generar con: openssl rand -base64 64
JWT_SECRET=
JWT_ALGO=HS256
JWT_TTL=1440           # Expiración del token de acceso: 1 día (en minutos)
JWT_REFRESH_TTL=20160  # Expiración del refresh token: 14 días (en minutos)
JWT_REFRESH_IAT=false
JWT_BLACKLIST_ENABLED=true         # Habilita invalidación de tokens en logout
JWT_BLACKLIST_GRACE_PERIOD=10      # Segundos de gracia para peticiones concurrentes
JWT_SHOW_BLACKLIST_EXCEPTION=false
JWT_LEEWAY=0

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

---

### Catalog Service (`catalog-service/.env`)

```env
APP_NAME=JD_CATALOG_SERVICE
APP_ENV=local
APP_KEY=                          # Generar con: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8001

# Base de datos (nombre del contenedor Docker como host)
DB_CONNECTION=mysql
DB_HOST=catalog-db
DB_PORT=3306
DB_DATABASE=catalog_db
DB_USERNAME=                      # Definir en docker-compose.yml
DB_PASSWORD=                      # Definir en docker-compose.yml

# JWT — debe ser igual en todos los servicios. Generar con: openssl rand -base64 64
JWT_SECRET=

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

> ⚠️ **Importante:** El valor de `JWT_SECRET` debe ser **idéntico** en los tres servicios (gateway, user-service, catalog-service) para que la validación de tokens funcione correctamente. Nunca subas archivos `.env` con credenciales reales a un repositorio público. Usa `.env.example` como plantilla.

---

## 🐳 Dockerfile

Todos los microservicios (**gateway**, **user-service**, **catalog-service**) comparten la misma estructura de Dockerfile basada en `php:8.2-apache`:

```dockerfile
FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql zip

# Habilitar módulo rewrite de Apache (necesario para que Laravel maneje rutas)
RUN a2enmod rewrite

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Instalar Composer desde imagen oficial
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Apuntar Apache al directorio /public de Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
```

**Extensiones PHP instaladas:**

| Extensión | Uso |
|---|---|
| `pdo_mysql` | Conexión a MySQL desde Laravel |
| `zip` | Requerida por Composer y Laravel para instalación de paquetes |

---

## 📡 Endpoints de la API

Todas las peticiones deben realizarse al **API Gateway** en `http://localhost:8080`. El Gateway las enruta internamente al microservicio correspondiente.

### 🔓 Rutas Públicas (no requieren autenticación)

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/health` | Estado del Gateway |
| `POST` | `/login` | Iniciar sesión → devuelve token JWT |
| `POST` | `/register` | Registrar nuevo usuario |

---

### 🔐 Rutas Privadas — Header requerido: `Authorization: Bearer <token>`

#### Autenticación

| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/logout` | Cerrar sesión e invalidar token en blacklist |
| `POST` | `/refresh` | Renovar token JWT antes de que expire |
| `GET` | `/me` | Datos del usuario actualmente autenticado |

#### 👤 Usuarios

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/users` | Listar todos los usuarios |
| `POST` | `/users` | Crear un nuevo usuario |
| `GET` | `/users/{id}` | Obtener usuario por ID |
| `PUT/PATCH` | `/users/{id}` | Actualizar usuario |
| `DELETE` | `/users/{id}` | Eliminar usuario |
| `GET` | `/users/general-data` | Datos generales / estadísticas de usuarios |

#### 🔒 Permisos y Roles

| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/permissions/general-data` | Listar permisos disponibles |
| `GET` | `/permissions/roles/{role}` | Obtener permisos asignados a un rol |
| `POST` | `/permissions/roles/{role}/assign` | Asignar permisos a un rol |

#### 🗂️ Catálogo — Productos

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/catalog/products` | Listar todos los productos |
| `POST` | `/catalog/products` | Crear producto |
| `GET` | `/catalog/products/{id}` | Obtener producto por ID |
| `PUT/PATCH` | `/catalog/products/{id}` | Actualizar producto |
| `DELETE` | `/catalog/products/{id}` | Eliminar producto |

#### 🗂️ Catálogo — Categorías

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/catalog/categories` | Listar categorías (soporta jerarquía padre-hijo) |
| `POST` | `/catalog/categories` | Crear categoría |
| `GET` | `/catalog/categories/{id}` | Obtener categoría por ID |
| `PUT/PATCH` | `/catalog/categories/{id}` | Actualizar categoría |
| `DELETE` | `/catalog/categories/{id}` | Eliminar categoría |

#### 🗂️ Catálogo — Marcas

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/catalog/brands` | Listar marcas |
| `POST` | `/catalog/brands` | Crear marca |
| `GET` | `/catalog/brands/{id}` | Obtener marca por ID |
| `PUT/PATCH` | `/catalog/brands/{id}` | Actualizar marca |
| `DELETE` | `/catalog/brands/{id}` | Eliminar marca |

#### 🗂️ Catálogo — Atributos y Valores

| Método | Endpoint | Descripción |
|---|---|---|
| `GET/POST` | `/catalog/attributes` | Listar / Crear atributos (ej: Talla, Color) |
| `GET/PUT/DELETE` | `/catalog/attributes/{id}` | Obtener / Editar / Eliminar atributo |
| `GET/POST` | `/catalog/attribute-values` | Listar / Crear valores (ej: XL, Rojo, 42) |
| `GET/PUT/DELETE` | `/catalog/attribute-values/{id}` | Obtener / Editar / Eliminar valor |

#### 🗂️ Catálogo — Variantes

| Método | Endpoint | Descripción |
|---|---|---|
| `GET/POST` | `/catalog/variants` | Listar / Crear variantes de producto |
| `GET/PUT/DELETE` | `/catalog/variants/{id}` | Obtener / Editar / Eliminar variante |

---

## 🗄️ Esquema de Base de Datos

### `user_db` — Servicio de Usuarios

```
┌──────────────────────┐      ┌──────────────────────────────┐
│        users         │      │    role_module_permissions   │
│──────────────────────│      │──────────────────────────────│
│ id                   │      │ id                           │
│ name                 │      │ role_id  ──────────────────▶ roles.id
│ last_name            │      │ module_id ─────────────────▶ modules.id
│ password             │      │ permission_id ─────────────▶ permissions.id
│ email                │      │ created_at                   │
│ phone                │      │ updated_at                   │
│ status               │      └──────────────────────────────┘
│ role_id  ────────────┼────▶ roles.id
│ remember_token       │
│ created_at           │      ┌──────────────────┐
│ updated_at           │      │    permissions   │
│ deleted_at           │      │──────────────────│
└──────────────────────┘      │ id               │
          │                   │ name             │
          ▼                   │ slug             │
┌──────────────────────┐      │ created_at       │
│     user_details     │      │ updated_at       │
│──────────────────────│      │ deleted_at       │
│ id                   │      └──────────────────┘
│ gender               │
│ birthdate            │      ┌──────────────────┐
│ address              │      │      roles       │
│ addon_address        │      │──────────────────│
│ notes                │      │ id               │
│ user_id  ────────────┼────▶ │ name             │
│ created_at           │      │ description      │
│ updated_at           │      │ status           │
│ deleted_at           │      │ created_at       │
└──────────────────────┘      │ updated_at       │
                              │ deleted_at       │
                              └──────────────────┘

┌───────────────────────────────────────────────────┐
│                    modules                        │
│───────────────────────────────────────────────────│
│ id · name · slug · icon · route · parent_id       │
│ is_active · sort_order · show_in_sidebar          │
│ created_at · updated_at · deleted_at              │
└───────────────────────────────────────────────────┘
```

**Tablas de `user_db`:**

| Tabla | Descripción |
|---|---|
| `users` | Usuarios del sistema con nombre, email, teléfono, estado y rol asignado |
| `user_details` | Datos extendidos del usuario: género, fecha de nacimiento, dirección |
| `roles` | Roles disponibles en el sistema (ej: Admin, Cliente) |
| `permissions` | Permisos individuales identificados por slug (ej: `products.create`) |
| `modules` | Módulos del sistema con soporte para sidebar y jerarquía padre-hijo |
| `role_module_permissions` | Tabla pivote: qué permisos tiene cada rol sobre cada módulo |

---

### `catalog_db` — Servicio de Catálogo

```
┌──────────────┐   ┌────────────────────────────┐   ┌──────────────────┐
│    brands    │   │          products           │   │   categories     │
│──────────────│   │────────────────────────────│   │──────────────────│
│ id           │◀──│ id                          │──▶│ id               │
│ name         │   │ name · slug                 │   │ name · slug      │
│ slug         │   │ description                 │   │ parent_id        │
│ is_active    │   │ short_description           │   │ is_active        │
│ created_at   │   │ category_id ───────────────▶│   │ created_at       │
│ updated_at   │   │ brand_id ──────────────────▶│   │ updated_at       │
│ deleted_at   │   │ product_status_id ─────────▶│   │ deleted_at       │
└──────────────┘   │ is_featured                 │   └──────────────────┘
                   │ created_at · updated_at      │
                   │ deleted_at                   │   ┌──────────────────────┐
                   └────────────────────────────┘    │   product_statuses   │
                                │                    │──────────────────────│
                                ▼                    │ id · name · slug     │
                   ┌────────────────────────────┐    │ is_active            │
                   │      product_variants       │    │ created_at           │
                   │────────────────────────────│    │ updated_at           │
                   │ id                          │    └──────────────────────┘
                   │ product_id                  │
                   │ sku · price · sale_price    │    ┌──────────────────────┐
                   │ stock · weight              │    │    product_images    │
                   │ length · width · height     │◀───│──────────────────────│
                   │ is_active                   │    │ id                   │
                   │ created_at · updated_at     │    │ product_id           │
                   │ deleted_at                  │    │ product_variant_id   │
                   └────────────────────────────┘    │ path · is_main       │
                                │                    │ sort_order           │
                                ▼                    │ created_at · updated_at│
                   ┌─────────────────────────────┐   │ deleted_at           │
                   │  variant_attribute_values   │   └──────────────────────┘
                   │─────────────────────────────│
                   │ id                          │
                   │ product_variant_id          │
                   │ attribute_value_id ─────────┼──▶ attribute_values.id
                   │ created_at · updated_at     │
                   │ deleted_at                  │
                   └─────────────────────────────┘

┌──────────────────────────┐      ┌──────────────────────┐
│     attribute_values     │      │      attributes      │
│──────────────────────────│      │──────────────────────│
│ id                       │      │ id                   │
│ attribute_id ────────────┼────▶ │ name                 │
│ value                    │      │ created_at           │
│ created_at · updated_at  │      │ updated_at           │
│ deleted_at               │      │ deleted_at           │
└──────────────────────────┘      └──────────────────────┘
```

**Tablas de `catalog_db`:**

| Tabla | Descripción |
|---|---|
| `products` | Productos principales: nombre, descripción, categoría, marca, estado, destacado |
| `product_variants` | Variantes por producto: SKU, precio, precio de oferta, stock, peso y dimensiones |
| `product_images` | Imágenes por producto y/o variante, con orden y flag de imagen principal |
| `product_statuses` | Estados del producto (ej: Activo, Agotado, Descontinuado) |
| `categories` | Categorías con soporte jerárquico (padre-hijo mediante `parent_id`) |
| `brands` | Marcas de los productos |
| `attributes` | Atributos de variante (ej: Talla, Color, Material) |
| `attribute_values` | Valores concretos de atributo (ej: XL, Rojo, 42) |
| `variant_attribute_values` | Tabla pivote: relaciona variantes con sus valores de atributo |

---

## 📁 Estructura del Proyecto

```
jkd-clothing/
├── docker-compose.yml              # Orquestación de todos los servicios
│
├── gateway/                        # API Gateway (Laravel)
│   ├── Dockerfile                  # PHP 8.2 + Apache
│   ├── .env                        # Variables del gateway
│   ├── app/
│   │   └── Http/
│   │       ├── Controllers/
│   │       │   └── GatewayController.php   # Proxy hacia user/catalog service
│   │       └── Middleware/
│   │           ├── CorsMiddleware.php
│   │           ├── RateLimitMiddleware.php
│   │           ├── LogRequestsMiddleware.php
│   │           └── AuthGatewayMiddleware.php
│   └── routes/
│       └── api.php                 # Rutas del gateway con middlewares
│
├── user-service/                   # Microservicio de usuarios (Laravel)
│   ├── Dockerfile
│   ├── .env
│   ├── app/
│   │   └── Http/Controllers/Api/
│   │       ├── AuthController.php       # login, logout, refresh, me
│   │       ├── UserController.php       # CRUD de usuarios
│   │       └── PermissionController.php # Gestión de roles y permisos
│   ├── database/
│   │   ├── migrations/                  # users, roles, permissions, modules, ...
│   │   └── seeders/
│   └── routes/
│       └── api.php
│
├── catalog-service/                # Microservicio de catálogo (Laravel)
│   ├── Dockerfile
│   ├── .env
│   ├── app/
│   │   └── Http/Controllers/Api/
│   │       ├── ProductController.php
│   │       ├── CategoryController.php
│   │       ├── BrandController.php
│   │       ├── AttributeController.php
│   │       ├── AttributeValueController.php
│   │       └── ProductVariantController.php
│   ├── database/
│   │   ├── migrations/                  # products, variants, images, categories, ...
│   │   └── seeders/
│   └── routes/
│       └── api.php
│
└── README.md
```

---

## 🌐 Puertos y Accesos

| Servicio | URL / Conexión |
|---|---|
| **API Gateway** | [http://localhost:8080](http://localhost:8080) |
| **Health Check** | [http://localhost:8080/health](http://localhost:8080/health) |
| **User Service** (directo) | [http://localhost:8000](http://localhost:8000) |
| **Catalog Service** (directo) | [http://localhost:8001](http://localhost:8001) |
| **phpMyAdmin** | [http://localhost:8081](http://localhost:8081) |
| **User DB** (cliente MySQL externo) | `localhost:3308` |
| **Catalog DB** (cliente MySQL externo) | `localhost:3307` |

### Credenciales phpMyAdmin

| Campo | Valor |
|---|---|
| Usuario | definido en `docker-compose.yml` → `MYSQL_USER` |
| Contraseña | definida en `docker-compose.yml` → `MYSQL_PASSWORD` |
| Servidores disponibles | `user-db` / `catalog-db` |

> En phpMyAdmin puedes seleccionar el servidor al iniciar sesión. Ambas bases de datos están disponibles desde la misma interfaz.

---

## 👥 Usuarios del Sistema

| Rol | Descripción | Acceso |
|---|---|---|
| **Cliente** | Usuario final que navega la tienda, consulta el catálogo y realiza compras | Rutas públicas + rutas autenticadas de compra |
| **Administrador** | Gestiona productos, categorías, marcas, inventario, precios y usuarios | Acceso completo al panel de administración |

Los permisos son **granulares por módulo**: cada rol puede tener distintos permisos (`ver`, `crear`, `editar`, `eliminar`) sobre cada módulo del sistema, configurables desde la tabla `role_module_permissions`.

---

## ⚠️ Riesgos y Consideraciones

| Riesgo | Descripción | Mitigación recomendada |
|---|---|---|
| **Caída de un servicio** | Si user-service o catalog-service falla, el gateway retornará error para esas rutas pero los demás servicios siguen operando | Configurar `restart: always` en Docker Compose; implementar health checks |
| **Falla en base de datos** | Una BD caída solo afecta a su propio microservicio | Bases de datos aisladas por servicio; configurar backups periódicos |
| **Alta concurrencia** | Con muchos usuarios simultáneos el rendimiento puede degradarse | El gateway aplica rate limiting (60 req/min); escalar horizontalmente con Docker Swarm o Kubernetes |
| **Seguridad del JWT** | Si el `JWT_SECRET` es débil o se expone, los tokens pueden ser forjados | Usar un secreto largo y aleatorio; rotarlo periódicamente; nunca subirlo al repositorio |
| **Exposición de BDs** | Los puertos 3307/3308 expuestos son convenientes para desarrollo | En producción, no exponer puertos MySQL al exterior; acceder solo desde la red interna Docker |
| **HTTP en producción** | La comunicación interna y externa es HTTP sin cifrar | En producción usar HTTPS con TLS (Let's Encrypt) para el gateway; comunicación interna puede permanecer en HTTP dentro de la red Docker |
| **CORS en producción** | `CORS_ALLOWED_ORIGINS` incluye `localhost` | Actualizar con los dominios reales del frontend antes del despliegue a producción |

---

## 🤝 Contribuciones

1. Haz un fork del repositorio
2. Crea una rama para tu feature:
   ```bash
   git checkout -b feature/nombre-de-la-funcionalidad
   ```
3. Realiza tus cambios y haz commit usando [Conventional Commits](https://www.conventionalcommits.org/):
   ```bash
   git commit -m "feat: descripción del cambio"
   # Tipos: feat | fix | docs | style | refactor | test | chore
   ```
4. Haz push a tu rama:
   ```bash
   git push origin feature/nombre-de-la-funcionalidad
   ```
5. Abre un **Pull Request** describiendo los cambios realizados

---

<p align="center">Desarrollado por JKD Clothing</p>
