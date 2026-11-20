# Atajos del servidor multiproyecto Laravel.
#
# Cárgalos en la terminal actual, desde la carpeta del repo:
#   source scripts/aliases.sh
#
# Para cargarlos siempre, añade esa misma línea con la ruta completa al final
# de ~/.bashrc, por ejemplo:
#   echo 'source ~/Proyecto_Semana_Sistemas/scripts/aliases.sh' >> ~/.bashrc
#
# (El script de setup NO modifica ~/.bashrc automáticamente.)

# Ruta del servidor: la carpeta del propio script (respeta un valor previo si ya existe)
if [[ -z "${SRV:-}" ]]; then
    SRV="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
fi
export SRV

# Shell dentro del contenedor como www-data (tu UID del host)
alias srv='docker compose -f "$SRV/compose.yaml" exec -u www-data servidor bash'

# Ejecutar un comando en un proyecto: lv <proyecto> <comando...>
lv() {
    if [[ $# -lt 2 ]]; then
        echo "Uso: lv <proyecto> <comando...>" >&2
        echo "Ejemplo: lv blog-semana-2026 php artisan migrate" >&2
        return 1
    fi

    docker compose -f "$SRV/compose.yaml" exec -u www-data \
        -w "/var/www/html/$1" servidor "${@:2}"
}

# Consola psql del contenedor db
alias psqlsrv='docker compose -f "$SRV/compose.yaml" exec db psql -U postgres'
