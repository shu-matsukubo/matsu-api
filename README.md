# matsu Backend

matsu のバックエンド API です。Laravel を Docker 上の PHP/Apache と MySQL で動かします。

Laravel アプリ本体は `src/www` 配下にあります。

## 技術スタック

- PHP
- Laravel
- MySQL
- Docker / Docker Compose
- Composer
- PHPUnit
- Laravel Pint
- Larastan

## 前提

- Docker Desktop がインストールされていること
- Docker Compose が利用できること
- ローカルの `18080` ポートと `13306` ポートが利用できること

PHP、Composer、MySQL は Docker コンテナ内で利用します。ローカルに PHP や Composer を入れなくても起動できます。

## コンテナ構成

`docker-compose.yml` で以下のコンテナを定義しています。

- `web`: PHP 8.4 + Apache
- `db`: MySQL 8.0

ポートは以下の通りです。

- API: `http://localhost:18080`
- MySQL: `localhost:13306`

## スクリプト

`scripts/` 配下に環境構築や更新用のスクリプトがあります。リポジトリのルートディレクトリで実行してください。

| スクリプト | 用途 |
|---|---|
| `scripts/setup.sh` | 初回セットアップ（下記「初回セットアップ」を参照） |
| `scripts/update.sh` | `git pull` 後の更新（依存関係・マイグレーション・シーダー） |
| `scripts/setup-hooks.sh` | Git フック（pre-commit / pre-push）の設置 |

```sh
sh scripts/setup.sh
sh scripts/update.sh
sh scripts/setup-hooks.sh
```

### Git フック

`setup-hooks.sh` は `.githooks/` のフックを `.git/hooks/` にコピーします。`setup.sh` 実行時にも自動で呼ばれます。

- **pre-commit**: ステージ済みの PHP ファイルに Pint でフォーマットを適用し、修正があれば再ステージします
- **pre-push**: push 対象の PHP ファイルに対して Pint のフォーマットチェックと PHPStan の静的解析を実行します（エラーがあれば push を中断）

いずれも Docker コンテナ（`web`）が起動している必要があります。

## 初回セットアップ

リポジトリをクローンしたら、ルートディレクトリで以下を実行します。

```sh
sh scripts/setup.sh
```

このスクリプトは以下を順に実行します。

1. `src/www/.env.local` を `src/www/.env` にコピー
2. Git フックの設置（`setup-hooks.sh`）
3. Docker イメージのビルド
4. コンテナの起動
5. `composer install`
6. マイグレーション
7. シーダー

### 手動でセットアップする場合

スクリプトを使わない場合は、以下の手順で同等の状態にできます。

```bash
docker compose up -d --build
```

Laravel アプリのディレクトリに入ります。

```bash
docker compose exec web bash
```

コンテナ内で依存関係をインストールします。

```bash
composer install
```

`.env` を作成します（`.env.local` に Docker 向けの DB 設定が入っています）。

```bash
cp .env.local .env
```

マイグレーションとシーダーを実行します。

```bash
php artisan migrate
php artisan db:seed
```

Git フックを使う場合は、コンテナから出たあとリポジトリのルートで以下を実行します。

```sh
sh scripts/setup-hooks.sh
```

## pull 後の更新

他メンバーの変更を取り込んだあとは、コンテナを起動した状態で以下を実行します。

```sh
sh scripts/update.sh
```

`composer install`、マイグレーション、シーダーをまとめて実行します。

## 起動

初回セットアップ後は、このリポジトリのルートディレクトリで以下を実行します。

```bash
docker compose up -d
```

API は `http://localhost:18080/api` で利用できます。

停止する場合は以下を実行します。

```bash
docker compose down
```

DB のデータを保持したまま停止する場合は通常の `docker compose down` で問題ありません。ボリュームも削除したい場合のみ `docker compose down -v` を使います。

## コードチェック

コンテナ内の `/var/www` で実行します。

```bash
composer pint:test
```

Laravel Pint でフォーマット差分をチェックします。

```bash
composer analyse
```

Larastan で静的解析を実行します。

```bash
composer test
```

PHPUnit を実行します。

## フォーマット

コンテナ内の `/var/www` で実行します。

```bash
composer pint
```

Laravel Pint で PHP コードをフォーマットします。

## 主なディレクトリ

- `src/www/app/Http/Controllers/Api`: API コントローラー
- `src/www/app/Services`: アプリケーションサービス
- `src/www/app/Queries`: DB 集計や検索処理
- `src/www/app/Models`: Eloquent モデル
- `src/www/app/Http/Resources`: API レスポンス整形
- `src/www/database/migrations`: テーブル定義
- `src/www/routes/api.php`: API ルート

## 主な API

- `GET /api/expenses`: 支出サマリー、履歴取得
- `POST /api/expenses`: 支出登録
- `GET /api/payment-methods`: 支払い方法一覧
- `GET /api/categories`: カテゴリ一覧
