# matsu API

Laravel API for the matsu workspace. The application runs in Docker with PHP/Apache and MySQL.

The Laravel app lives under `src/www`.

## Tech Stack

- PHP 8.4
- Laravel 13
- MySQL 8.0
- Docker / Docker Compose
- Composer
- PHPUnit
- Laravel Pint
- PHPStan / Larastan

## Local Setup

From the repository root:

```bash
sh scripts/setup.sh
```

The setup script:

1. Copies `src/www/.env.local` to `src/www/.env`.
2. Installs Git hooks.
3. Builds Docker images.
4. Starts containers.
5. Runs `composer install`.
6. Runs migrations.
7. Runs seeders.

## Daily Start / Stop

```bash
docker compose up -d
docker compose down
```

The API is available at:

```text
http://localhost:18080/api
```

MySQL is exposed at:

```text
localhost:13306
```

## Containers

- `web`: PHP 8.4 + Apache, mounted at `/var/www`.
- `db`: MySQL 8.0.

Database defaults:

```text
database: matsu
user: test_user
password: test_user_pass
root password: test_root_pass
```

## Update After Pull

From the repository root:

```bash
sh scripts/update.sh
```

This runs `composer install`, migrations, and seeders inside the Docker environment.

## Quality Checks

Run these inside the `web` container from `/var/www`, or through `docker compose exec web ...` from the repository root.

```bash
composer pint:test
composer analyse
composer test
```

Format PHP code with:

```bash
composer pint
```

## Git Hooks

Install hooks with:

```bash
sh scripts/setup-hooks.sh
```

Hooks are copied from `.githooks/` into `.git/hooks/`.

- `pre-commit`: formats staged PHP files with Pint in the `web` container and re-stages changed files.
- `pre-push`: runs Pint and PHPStan for pushed PHP diffs in the `web` container. If Pint changes files, the push is stopped so the changes can be reviewed and committed.

The API Docker containers must be running for the hooks to work.

## CI

GitHub Actions workflow:

```text
.github/workflows/ci.yml
```

CI runs on pull requests to `main` and performs:

- Composer install
- `.env.testing` setup
- Migrations
- Seeders
- Pint check
- PHPStan / Larastan
- PHPUnit

PHPStan currently uses `continue-on-error: true` in CI.

## Authentication

API requests are protected by JWT middleware. Tokens are issued by `matsu-auth`, and this API verifies them using the auth server JWKS endpoint.

Important local auth settings:

```text
AUTH_SERVER_ISSUER=http://localhost:18081
AUTH_SERVER_AUDIENCE=matsu-api
AUTH_SERVER_JWKS_URL=http://host.docker.internal:18081/.well-known/jwks.json
AUTH_SERVER_JWKS_CACHE_SECONDS=3600
AUTH_SERVER_CACHE_STORE=database
```

If auth-related environment values change, clear Laravel config cache:

```bash
php artisan config:clear
```

## Main Directories

- `src/www/routes/api.php`: API routes.
- `src/www/app/Http/Controllers/Api`: API controllers.
- `src/www/app/Http/Middleware`: HTTP middleware, including JWT auth.
- `src/www/app/Http/Resources`: API response resources.
- `src/www/app/Services`: Application services.
- `src/www/app/Queries`: Query classes.
- `src/www/app/Models`: Eloquent models.
- `src/www/app/Support`: Shared support utilities.
- `src/www/database/migrations`: Database migrations.

## Main Endpoints

- `GET /api/expenses`
- `POST /api/expenses`
- `GET /api/payment-methods`
- `GET /api/categories`
