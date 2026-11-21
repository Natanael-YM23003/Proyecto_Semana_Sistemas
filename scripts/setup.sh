#!/usr/bin/env bash
#
# setup.sh: Prepara el entorno de desarrollo del servidor multiproyecto Laravel.
#
# Uso (dentro de Ubuntu, NO en /mnt/c):
#   cd <carpeta del repo>
#   ./scripts/setup.sh
#
# Es idempotente: se puede volver a ejecutar sin perder datos ni duplicar nada.
#
set -euo pipefail

SRV_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$SRV_DIR"

PROYECTO="blog-semana-2026"
NOMBRE_APP="Blog Semana de Sistemas 2026"
DB_NAME="blog_semana_2026"
DB_USER="postgres"
DB_PASS="root123"
APP_URL="http://${PROYECTO}.localhost:8083"
PROYECTO_DIR="www/${PROYECTO}"
CONTENEDOR="servidor"

paso()  { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }
ok()    { printf '\033[1;32m  [ok] %s\033[0m\n' "$*"; }
aviso() { printf '\033[1;33m  [aviso] %s\033[0m\n' "$*"; }
morir() { printf '\n\033[1;31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }

# ---------------------------------------------------------------------------
# 0. Comprobaciones previas
# ---------------------------------------------------------------------------
paso "Comprobando requisitos"

command -v docker >/dev/null 2>&1 \
    || morir "No se encontró 'docker'. Activa la integración WSL de Docker Desktop para Ubuntu-24.04 (Settings > Resources > WSL Integration)."

docker info >/dev/null 2>&1 \
    || morir "Docker no responde. Abre Docker Desktop y espera a que termine de arrancar."

[[ -f .env ]] || morir "Falta el archivo .env del servidor (cópialo de .env.example y pon HOST_UID/HOST_GID)."

set -a
# shellcheck disable=SC1091
source ./.env
set +a

[[ -n "${HOST_UID:-}" && -n "${HOST_GID:-}" ]] \
    || morir "HOST_UID y HOST_GID deben estar definidos en .env (id -u / id -g)."

if [[ "$(id -u)" != "$HOST_UID" ]]; then
    aviso "Tu UID actual ($(id -u)) no coincide con HOST_UID=$HOST_UID del .env."
    aviso "Corrige .env y reconstruye la imagen: docker compose build --no-cache servidor"
fi

if [[ "$(id -g)" != "$HOST_GID" ]]; then
    aviso "Tu GID actual ($(id -g)) no coincide con HOST_GID=$HOST_GID del .env."
fi

ok "Docker disponible: $(docker --version)"

# El bind mount ./www:/var/www/html debe existir antes de 'up' y pertenecer
# al usuario del host (UID 1000), no a root.
mkdir -p www

# ---------------------------------------------------------------------------
# 1. Construir y levantar
# ---------------------------------------------------------------------------
paso "Construyendo la imagen del servidor (PHP 8.4 + Apache + Node 24 + Composer)"
docker compose build

paso "Levantando los contenedores"
docker compose up -d

paso "Esperando a que PostgreSQL esté healthy"
intentos=0
while true; do
    estado="$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}sin-healthcheck{{end}}' servidor_postgres 2>/dev/null || true)"

    if [[ "$estado" == "healthy" ]]; then
        ok "PostgreSQL está healthy"
        break
    fi

    intentos=$((intentos + 1))
    if (( intentos > 60 )); then
        docker compose logs --tail=50 db || true
        morir "PostgreSQL no llegó a 'healthy' en 2 minutos. Revisa los logs de arriba."
    fi

    sleep 2
done

# ---------------------------------------------------------------------------
# 2. Base de datos del proyecto
# ---------------------------------------------------------------------------
paso "Verificando la base de datos '${DB_NAME}'"

existe="$(docker compose exec -T db psql -U "$DB_USER" -tAc \
    "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | tr -d '[:space:]')"

if [[ "$existe" == "1" ]]; then
    ok "La base '${DB_NAME}' ya existe"
else
    docker compose exec -T db psql -U "$DB_USER" -v ON_ERROR_STOP=1 \
        -c "CREATE DATABASE ${DB_NAME};"
    ok "Base '${DB_NAME}' creada"
fi

# ---------------------------------------------------------------------------
# 3. Proyecto Laravel 12
# ---------------------------------------------------------------------------
if [[ -f "${PROYECTO_DIR}/artisan" ]]; then
    ok "El proyecto ${PROYECTO} ya existe"
else
    paso "Creando Laravel 12 en ${PROYECTO_DIR} (Composer como www-data)"
    docker compose exec -T -u www-data "$CONTENEDOR" \
        composer create-project "laravel/laravel:^12.0" "$PROYECTO" \
        --no-interaction --prefer-dist
    ok "Proyecto creado"
fi

ENV_FILE="${PROYECTO_DIR}/.env"

if [[ ! -f "$ENV_FILE" ]]; then
    cp "${PROYECTO_DIR}/.env.example" "$ENV_FILE"
fi

# ---------------------------------------------------------------------------
# 4. Configurar .env del proyecto
# ---------------------------------------------------------------------------
paso "Configurando ${ENV_FILE}"

set_env() {
    local clave="$1" valor="$2" archivo="$3"

    if grep -qE "^[[:space:]]*#?[[:space:]]*${clave}=" "$archivo"; then
        sed -i -E "s|^[[:space:]]*#?[[:space:]]*${clave}=.*|${clave}=${valor}|" "$archivo"
    else
        printf '%s=%s\n' "$clave" "$valor" >> "$archivo"
    fi
}

set_env APP_NAME        "\"${NOMBRE_APP}\"" "$ENV_FILE"
set_env APP_ENV         "local"                "$ENV_FILE"
set_env APP_DEBUG       "true"                 "$ENV_FILE"
set_env APP_URL         "$APP_URL"             "$ENV_FILE"
set_env APP_TIMEZONE    "America/El_Salvador"  "$ENV_FILE"
set_env APP_LOCALE      "es"                   "$ENV_FILE"
set_env APP_FALLBACK_LOCALE "en"               "$ENV_FILE"
set_env APP_FAKER_LOCALE "es_ES"               "$ENV_FILE"
set_env DB_CONNECTION   "pgsql"                "$ENV_FILE"
set_env DB_HOST         "db"                   "$ENV_FILE"
set_env DB_PORT         "5432"                 "$ENV_FILE"
set_env DB_DATABASE     "$DB_NAME"             "$ENV_FILE"
set_env DB_USERNAME     "$DB_USER"             "$ENV_FILE"
set_env DB_PASSWORD     "$DB_PASS"             "$ENV_FILE"
set_env SESSION_DRIVER  "database"             "$ENV_FILE"
set_env CACHE_STORE     "database"             "$ENV_FILE"
set_env QUEUE_CONNECTION "database"            "$ENV_FILE"

# Laravel crea el proyecto con SQLite; con PostgreSQL ese archivo no se usa.
rm -f "${PROYECTO_DIR}/database/database.sqlite"

ok "Variables de conexión y APP_URL ajustadas"

# ---------------------------------------------------------------------------
# 5. Vite (bloque server del PDF)
# ---------------------------------------------------------------------------
VITE_CONFIG="${PROYECTO_DIR}/vite.config.js"

if [[ -f "$VITE_CONFIG" ]] && grep -q "strictPort: true" "$VITE_CONFIG"; then
    ok "vite.config.js ya tiene el bloque server"
else
    aviso "Escribiendo vite.config.js con el bloque server del PDF"
    cat > "$VITE_CONFIG" <<'VITE'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: 'http://localhost:5173',
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
});
VITE
fi

# ---------------------------------------------------------------------------
# 6. Key, storage:link y migraciones (siempre como www-data)
# ---------------------------------------------------------------------------
paso "Generando APP_KEY (si falta) y preparando Laravel"

ejecutar_en_proyecto() {
    docker compose exec -T -u www-data -w "/var/www/html/${PROYECTO}" "$CONTENEDOR" "$@"
}

# El proyecto viene en el repo sin vendor/ porque está en .gitignore.
if [[ ! -d "${PROYECTO_DIR}/vendor" ]]; then
    paso "Instalando dependencias de Composer"
    ejecutar_en_proyecto composer install --no-interaction --prefer-dist
    ok "Dependencias instaladas"
fi

if ! grep -qE '^APP_KEY=base64:.+' "$ENV_FILE"; then
    ejecutar_en_proyecto php artisan key:generate --force
    ok "APP_KEY generada"
else
    ok "APP_KEY ya existe"
fi

ejecutar_en_proyecto php artisan config:clear >/dev/null

if [[ ! -e "${PROYECTO_DIR}/public/storage" && ! -L "${PROYECTO_DIR}/public/storage" ]]; then
    ejecutar_en_proyecto php artisan storage:link >/dev/null
    ok "Enlace public/storage creado"
else
    ok "Enlace public/storage ya existe"
fi

paso "Ejecutando migraciones contra PostgreSQL"
ejecutar_en_proyecto php artisan migrate --force

paso "Cargando categorías base"
ejecutar_en_proyecto php artisan db:seed --class=CategorySeeder --force

# ---------------------------------------------------------------------------
# 7. Dependencias de Node y build
# ---------------------------------------------------------------------------
paso "Instalando dependencias de Node (npm install)"
docker compose exec -T -u www-data -w "/var/www/html/${PROYECTO}" "$CONTENEDOR" npm install

paso "Compilando assets (npm run build)"
docker compose exec -T -u www-data -w "/var/www/html/${PROYECTO}" "$CONTENEDOR" npm run build

# ---------------------------------------------------------------------------
# 8. Resumen
# ---------------------------------------------------------------------------
uid_www="$(docker compose exec -T "$CONTENEDOR" id -u www-data | tr -d '[:space:]')"

printf '\n\033[1;32m============================================================\033[0m\n'
printf '\033[1;32m  Servidor listo\033[0m\n'
printf '\033[1;32m============================================================\033[0m\n\n'

printf '  Indice de proyectos : http://localhost:8083\n'
printf '  Blog                : %s\n' "$APP_URL"
printf '  Vite (dev)          : http://localhost:5173\n'
printf '  pgAdmin             : http://localhost:8087\n'
printf '  PostgreSQL (host)   : localhost:5433  (dentro de Docker: db:5432)\n\n'

printf '  pgAdmin             : admin@example.com / admin123\n'
printf '  PostgreSQL          : postgres / root123  (base: %s)\n\n' "$DB_NAME"

printf '  Usuario Ubuntu (host) : UID %s / GID %s\n' "$HOST_UID" "$HOST_GID"
printf '  www-data (contenedor) : UID %s\n' "$uid_www"

if [[ "$uid_www" != "$HOST_UID" ]]; then
    printf '\n  \033[1;33mADVERTENCIA: el UID de www-data no coincide con HOST_UID.\033[0m\n'
    printf '  Reconstruye con: docker compose build --no-cache servidor && docker compose up -d\n'
fi

cat <<'AYUDA'

  Atajos (una vez por terminal, o añádelos a ~/.bashrc con "source"):
    source scripts/aliases.sh

  Comandos diarios (desde la carpeta del repo):
    docker compose ps                  # estado de los servicios
    docker compose up -d               # arrancar
    docker compose stop                # detener sin borrar datos
    docker compose logs -f servidor    # errores de Apache/PHP
    lv blog-semana-2026 php artisan migrate
    lv blog-semana-2026 npm run dev    # un solo proyecto a la vez (5173)
    psqlsrv -d blog_semana_2026 -c "\dt"

  Respaldos:
    docker compose exec db pg_dump -U postgres blog_semana_2026 > respaldo.sql

AYUDA
