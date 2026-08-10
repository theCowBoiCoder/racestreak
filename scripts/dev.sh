#!/usr/bin/env sh

set -eu

command_name="${1:-start}"
confirmation="${2:-}"
repository_root="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

cd "$repository_root"

if ! docker info >/dev/null 2>&1; then
    echo "Docker is not available. Start Docker Desktop and try again." >&2
    exit 1
fi

case "$command_name" in
    start)
        docker compose up --detach --build
        docker compose ps
        ;;
    stop)
        docker compose down --remove-orphans
        ;;
    reset)
        if [ "$confirmation" != "--yes" ]; then
            echo "Reset deletes the local PostgreSQL volume. Re-run with --yes to confirm." >&2
            exit 1
        fi

        docker compose down --volumes --remove-orphans
        docker compose up --detach --build
        docker compose ps
        ;;
    status)
        docker compose ps
        ;;
    test)
        docker compose --profile tools build backend-test frontend-test
        docker compose --profile tools run --rm backend-test
        docker compose --profile tools run --rm frontend-test
        ;;
    logs)
        docker compose logs --follow backend frontend database
        ;;
    *)
        echo "Usage: $0 {start|stop|reset|status|test|logs} [--yes]" >&2
        exit 1
        ;;
esac
