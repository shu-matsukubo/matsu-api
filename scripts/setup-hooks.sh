#!/bin/sh

SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
ROOT_DIR=$(dirname "$SCRIPT_DIR")
HOOKS_DIR="$ROOT_DIR/.githooks"
GIT_HOOKS_DIR="$ROOT_DIR/.git/hooks"

echo "Gitフックをセットアップします..."

for hook in "$HOOKS_DIR"/*; do
  hook_name=$(basename "$hook")
  cp "$hook" "$GIT_HOOKS_DIR/$hook_name"
  chmod +x "$GIT_HOOKS_DIR/$hook_name"
  echo "$hook_name を設置しました"
done

echo ""
echo "セットアップ完了！"
