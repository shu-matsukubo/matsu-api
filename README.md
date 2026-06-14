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

## 初回セットアップ

このリポジトリのルートディレクトリで実行します。

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

`.env` を作成します。

```bash
cp .env.example .env
```

Docker の MySQL に接続する場合は、`.env` の DB 設定を以下のように変更します。

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=matsu
DB_USERNAME=test_user
DB_PASSWORD=test_user_pass
```

アプリケーションキーを生成します。

```bash
php artisan key:generate
```

マイグレーションを実行します。

```bash
php artisan migrate
```

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

```bash
composer check
```

Pint、Larastan、PHPUnit をまとめて実行します。

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
