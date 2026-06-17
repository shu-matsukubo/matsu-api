#!/bin/sh
set -e

SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
PROJECT_ROOT=$(cd "$SCRIPT_DIR/.." && pwd)

cd "$PROJECT_ROOT"

echo "=== matsu セットアップ開始 ==="

echo ""
echo "[1/7] .env.local を .env にコピーします..."
cp src/www/.env.local src/www/.env

echo ""
echo "[2/7] Gitフックをセットアップします..."
bash scripts/setup-hooks.sh

echo ""
echo "[3/7] Dockerイメージをビルドします..."
docker compose build --no-cache

echo ""
echo "[4/7] コンテナを起動します..."
docker compose up -d

echo ""
echo "[5/7] composer install を実行します..."
docker compose exec web composer install --no-interaction

echo ""
echo "[6/7] マイグレーションを実行します..."

echo ""
echo "DBの起動を待機中..."
until docker compose exec db mysqladmin ping -h localhost -u root -ptest_root_pass --silent 2>/dev/null; do
  printf "."
  sleep 2
done
echo " DB起動完了"

docker compose exec web php artisan migrate --force

echo ""
echo "[7/7] シーダーを実行します..."
docker compose exec web php artisan db:seed

echo ""
echo "=== セットアップ完了 ==="
