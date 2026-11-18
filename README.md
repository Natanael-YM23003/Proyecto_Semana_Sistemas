# Entorno de desarrollo Laravel

Entorno local con Apache, PHP 8.4, Node.js 24, PostgreSQL 17 y pgAdmin. Permite ejecutar varios proyectos Laravel en `www/`, cada uno bajo su propio subdominio.

## Requisitos

- Windows con WSL2 y Ubuntu 24.04.
- Virtualización activada en la BIOS o UEFI (VT-x en Intel, AMD-V en AMD).
- Docker Desktop con el motor WSL2 y la integración de Ubuntu activados.
- Git instalado en Ubuntu.

Clona el repositorio dentro del sistema de archivos de Ubuntu (por ejemplo, en `~/`), no en `/mnt/c/`.

## Instalación

Desde Ubuntu:

```bash
git clone https://github.com/Natanael-YM23003/Proyecto_Semana_Sistemas.git
cd Proyecto_Semana_Sistemas
printf 'HOST_UID=%s\nHOST_GID=%s\n' "$(id -u)" "$(id -g)" > .env
chmod +x scripts/*.sh
./scripts/setup.sh
```

El setup construye la imagen, inicia los servicios y prepara `blog-semana-2026`, que ya viene en `www/`: dependencias, `.env`, migraciones y assets compilados. Para comprobar el entorno:

```bash
./scripts/verify.sh
```

## Estructura

- `compose.yaml`: los tres servicios (servidor, PostgreSQL y pgAdmin).
- `docker/`: Dockerfile, Apache, PHP, pgAdmin e índice de proyectos.
- `scripts/`: instalación, verificación y atajos.
- `www/blog-semana-2026/`: el proyecto Laravel 12, todavía sin los módulos del blog.
- `.env.example`: plantilla del `.env` del servidor (UID/GID).

## Direcciones locales

| Servicio | Dirección |
|---|---|
| Índice de proyectos | <http://localhost:8083> |
| Blog de ejemplo | <http://blog-semana-2026.localhost:8083> |
| pgAdmin | <http://localhost:8087> |
| PostgreSQL desde Windows | `localhost:5433` |
| PostgreSQL desde Docker | `db:5432` |
| Vite | <http://localhost:5173> |

- pgAdmin: `admin@example.com` / `admin123`
- PostgreSQL: `postgres` / `root123`

Estas credenciales son solo para desarrollo local; no las uses en producción.

## Uso diario

```bash
docker compose up -d       # iniciar
docker compose ps          # ver estado
docker compose logs -f     # ver registros
docker compose down        # detener contenedores
```

Los datos de PostgreSQL y pgAdmin se guardan en volúmenes Docker. `docker compose down` no los elimina; `docker compose down -v` sí.

Para cargar los atajos de `scripts/aliases.sh` en una terminal:

```bash
source scripts/aliases.sh
```

`lv <proyecto> <comando...>` ejecuta comandos en un proyecto como `www-data`; por ejemplo:

```bash
lv blog-semana-2026 php artisan migrate
lv blog-semana-2026 npm run dev
```

Cada proyecto nuevo en `www/` se publica en `http://<nombre>.localhost:8083`. El índice usa su carpeta `public/`; Vite comparte el puerto 5173, así que solo un proyecto puede ejecutar `npm run dev` a la vez.

## Crear otro proyecto

No hay que tocar Apache: cualquier carpeta en `www/` con un `public/index.php` aparece sola en el índice. Para crear uno, entra a la carpeta del repo y ejecuta:

```bash
docker compose exec -u www-data servidor \
  composer create-project laravel/laravel:^12.0 mi-proyecto
```

Después ajusta su `.env` con `DB_CONNECTION=pgsql`, `DB_HOST=db`, una base propia y su `APP_URL`, crea la base y migra:

```bash
docker compose exec db createdb -U postgres mi_proyecto
lv mi-proyecto php artisan key:generate
lv mi-proyecto php artisan migrate
```

Si la carpeta no tiene `public/`, el índice la muestra igual pero sin enlace.

## Vite

`npm run dev` levanta Vite en el 5173 y deja `public/hot`. Mientras ese archivo exista, Laravel sirve los assets desde el servidor de desarrollo; no lo borres con Vite corriendo.

```bash
lv blog-semana-2026 npm run dev
```

Para dejar el proyecto con assets compilados y sin Vite levantado:

```bash
lv blog-semana-2026 npm run build
rm -f www/blog-semana-2026/public/hot
```

Si una página se ve sin estilos o intenta conectarse al 5173 sin éxito, casi siempre es un `public/hot` que quedó de una sesión anterior.

## pgAdmin

Abre <http://localhost:8087> con `admin@example.com` y `admin123`. El servidor `PostgreSQL Docker` ya está registrado y apunta a `db:5432`; al conectarlo pide la contraseña `root123`. Ahí puedes ver las tablas, ejecutar SQL y revisar los datos de `blog_semana_2026`.

## Respaldos

```bash
# respaldar
docker compose exec -T db pg_dump -U postgres blog_semana_2026 > respaldo.sql

# restaurar en una base vacía
docker compose exec -T db createdb -U postgres blog_semana_2026
docker compose exec -T db psql -U postgres -d blog_semana_2026 < respaldo.sql
```

Los volúmenes `postgres_data` y `pgadmin_data` guardan los datos entre reinicios. `docker compose down` no los borra; `docker compose down -v` sí, así que respalda antes de usar `-v`.

## Problemas frecuentes

| Problema | Causa | Solución |
|---|---|---|
| `port is already allocated` | Otro contenedor o proceso usa el puerto | `docker compose down` y revisa qué queda con `docker compose ps` |
| PostgreSQL no llega a `healthy` | Docker Desktop acaba de arrancar o el volumen quedó a medias | Espera un momento y repite `./scripts/setup.sh` |
| Archivos nuevos salen como `root` | Se ejecutó el comando sin `-u www-data` | Usa `lv` o `srv`; para reparar: `docker compose exec -u root servidor chown -R www-data:www-data /var/www/html/<proyecto>` |
| `Permission denied` en `storage/` o `bootstrap/cache` | Permisos cambiados a mano | Corrige el dueño como arriba, sin usar `chmod 777` |
| La página se ve sin estilos | Quedó `public/hot` de un `npm run dev` | Bórralo y vuelve a correr `npm run dev` o `npm run build` |
| `www-data` no coincide con tu UID | `.env` con otro valor o imagen vieja | Ajusta `HOST_UID`/`HOST_GID` y reconstruye con `docker compose build --no-cache servidor` |
