#!/usr/bin/env bash
set -euo pipefail

STACK_DIR="${STACK_DIR:-/opt/stacks/wp-loja-despertando}"
cd "$STACK_DIR"

TS="$(date +%Y%m%d%H%M%S)"
DEST="backups/$TS"
mkdir -p "$DEST"
chmod 700 "$DEST"

# Database dump. The command runs inside the db container, so secrets stay inside Docker/env scope.
docker compose exec -T db sh -lc 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' > "$DEST/database.sql"

# WordPress data and operational config without copying runtime secrets to the repository.
tar -czf "$DEST/wp-data.tar.gz" -C "$STACK_DIR" wp-data
cp -a compose.yaml "$DEST/compose.yaml"

sha256sum "$DEST"/* > "$DEST/SHA256SUMS"

echo "backup_dir=$DEST"
