#!/bin/sh
set -e

SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
PROJECT_ROOT=$(cd "$SCRIPT_DIR/.." && pwd)

cd "$PROJECT_ROOT"

echo "=== matsu アップデート開始 ==="

echo ""
echo "[1/3] composer install を実行します..."
docker compose exec web composer install --no-interaction

echo ""
echo "[2/3] マイグレーションを実行します..."
docker compose exec web php artisan migrate --force

echo ""
echo "[3/3] シーダーを実行します..."
docker compose exec web php artisan db:seed

echo ""
echo "=== アップデート完了 ==="
