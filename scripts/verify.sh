#!/usr/bin/env bash
#
# verify.sh: Comprueba el estado del servidor multiproyecto y del blog.
# Uso: ./scripts/verify.sh (desde la carpeta del repo)
#
set -uo pipefail

SRV_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$SRV_DIR"

PROYECTO="blog-semana-2026"
DB_NAME="blog_semana_2026"
FALLOS=0

ok()    { printf '\033[1;32m  [ok]\033[0m %s\n' "$*"; }
fallo() { printf '\033[1;31m  [FALLO]\033[0m %s\n' "$*"; FALLOS=$((FALLOS + 1)); }
info()  { printf '\n\033[1;36m%s\033[0m\n' "$*"; }

# ---------------------------------------------------------------------------
info "Contenedores"
docker compose ps
estado_db="$(docker inspect -f '{{.State.Health.Status}}' servidor_postgres 2>/dev/null || echo desconocido)"

if [[ "$estado_db" == "healthy" ]]; then
    ok "servidor_postgres está healthy"
else
    fallo "servidor_postgres no está healthy (estado: $estado_db)"
fi

for c in servidor_laravel servidor_postgres servidor_pgadmin; do
    if [[ "$(docker inspect -f '{{.State.Running}}' "$c" 2>/dev/null || echo false)" == "true" ]]; then
        ok "$c está corriendo"
    else
        fallo "$c no está corriendo"
    fi
done

# ---------------------------------------------------------------------------
info "Versiones y extensiones de PHP"
docker compose exec -T servidor php -v | head -1
docker compose exec -T servidor node -v
docker compose exec -T servidor composer -V
docker compose exec -T servidor npm -v

extensiones="$(docker compose exec -T servidor php -m | tr -d '\r')"
for ext in pdo_pgsql pgsql; do
    if grep -qix "$ext" <<<"$extensiones"; then
        ok "extensión $ext presente"
    else
        fallo "falta la extensión $ext"
    fi
done

# ---------------------------------------------------------------------------
info "UID/GID"
uid_host="$(id -u)"
gid_host="$(id -g)"
uid_www="$(docker compose exec -T servidor id -u www-data | tr -d '\r')"
gid_www="$(docker compose exec -T servidor id -g www-data | tr -d '\r')"

if [[ "$uid_www" == "$uid_host" && "$gid_www" == "$gid_host" ]]; then
    ok "www-data = UID $uid_www / GID $gid_www (igual que el host)"
else
    fallo "www-data = UID $uid_www / GID $gid_www, host = UID $uid_host / GID $gid_host"
fi

uid_env="$(stat -c %u "www/$PROYECTO/.env" 2>/dev/null || echo ausente)"
if [[ "$uid_env" == "$uid_host" ]]; then
    ok "www/$PROYECTO/.env pertenece a UID $uid_env (no root)"
else
    fallo "www/$PROYECTO/.env pertenece a UID $uid_env (se esperaba $uid_host)"
fi

# ---------------------------------------------------------------------------
info "HTTP"
codigo_indice="$(curl -s -m 15 -o /dev/null -w '%{http_code}' http://127.0.0.1:8083/ || true)"
if [[ "$codigo_indice" == "200" ]]; then
    ok "http://localhost:8083 -> 200"
else
    fallo "http://localhost:8083 -> ${codigo_indice:-sin respuesta}"
fi

html_blog="$(curl -s -m 15 --resolve "${PROYECTO}.localhost:8083:127.0.0.1" \
    "http://${PROYECTO}.localhost:8083/" || true)"
codigo_blog="$(curl -s -m 15 -o /dev/null -w '%{http_code}' \
    --resolve "${PROYECTO}.localhost:8083:127.0.0.1" \
    "http://${PROYECTO}.localhost:8083/" || true)"

if [[ "$codigo_blog" == "200" ]]; then
    ok "http://${PROYECTO}.localhost:8083 -> 200"
else
    fallo "http://${PROYECTO}.localhost:8083 -> ${codigo_blog:-sin respuesta}"
fi

if grep -qi "laravel" <<<"$html_blog"; then
    ok "La welcome page de Laravel responde"
else
    fallo "La página del blog no parece la welcome page de Laravel"
fi

# pgAdmin tarda en arrancar la primera vez (inicializa el volumen).
codigo_pgadmin=""
for _ in {1..30}; do
    codigo_pgadmin="$(curl -s -m 10 -o /dev/null -w '%{http_code}' http://127.0.0.1:8087/ || true)"
    [[ "$codigo_pgadmin" == "200" || "$codigo_pgadmin" == "302" ]] && break
    sleep 3
done

if [[ "$codigo_pgadmin" == "200" || "$codigo_pgadmin" == "302" ]]; then
    ok "http://localhost:8087 -> $codigo_pgadmin"
else
    fallo "http://localhost:8087 -> ${codigo_pgadmin:-sin respuesta}"
fi

# ---------------------------------------------------------------------------
info "PostgreSQL y migraciones"
docker compose exec -T db psql -U postgres -tAc "SELECT version();" | head -1

docker compose exec -T -u www-data -w "/var/www/html/${PROYECTO}" servidor \
    php artisan migrate:status

tablas="$(docker compose exec -T db psql -U postgres -d "$DB_NAME" -tAc \
    "SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename;" | tr -d '\r')"

for tabla in users sessions cache jobs migrations; do
    if grep -qx "$tabla" <<<"$tablas"; then
        ok "tabla $tabla existe"
    else
        fallo "falta la tabla $tabla"
    fi
done

# ---------------------------------------------------------------------------
info "Vite"
if [[ -f "www/$PROYECTO/public/build/manifest.json" ]]; then
    ok "assets compilados (public/build/manifest.json)"
else
    fallo "faltan los assets compilados (npm run build)"
fi

if grep -q "strictPort: true" "www/$PROYECTO/vite.config.js" 2>/dev/null; then
    ok "vite.config.js tiene el bloque server"
else
    fallo "vite.config.js no tiene el bloque server"
fi

# ---------------------------------------------------------------------------
printf '\n'
if (( FALLOS == 0 )); then
    printf '\033[1;32mTodo correcto. Servidor listo.\033[0m\n'
else
    printf '\033[1;31m%d comprobación(es) fallaron.\033[0m\n' "$FALLOS"
    exit 1
fi
