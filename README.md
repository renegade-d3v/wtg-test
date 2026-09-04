# WTG Test API

REST API for asynchronous accommodation offer imports, cheapest-offer search, and safe reservations.

## Stack

- **Laravel 12** + PHP 8.4
- **MySQL 8** (persisted via Docker volume)
- **Redis 7** — queue driver (dedicated `imports` queue)
- **Docker** — nginx + php-fpm + mysql + redis
- **Pest** — feature tests
- **L5-Swagger** — OpenAPI documentation

## Getting started

```bash
git clone git@github.com:renegade-d3v/wtg-test.git
cd wtg-test
cp .env.example .env
```

### With Docker (recommended)

```bash
# First run — builds image, waits for MySQL, runs migrations automatically
docker compose up -d --build

# Subsequent starts
docker compose up -d

# Stop
docker compose down
```

App is available at **http://localhost**
Swagger UI: **http://localhost/api/documentation**

### Without Docker

Requires a local MySQL 8 and Redis instance; point `DB_HOST`/`REDIS_HOST` in `.env` at them (e.g. `127.0.0.1`), then:

```bash
composer setup   # install deps, copy .env, generate key, migrate, npm install & build
composer dev     # start php artisan serve + queue worker + vite concurrently
```

## API endpoints

| Method | Path | Description |
|--------|------|--------------|
| `POST` | `/api/imports` | Queue a new offers import |
| `GET` | `/api/imports/{import}` | Get the current status of an import |
| `GET` | `/api/properties` | Search properties by their cheapest current offer |
| `POST` | `/api/offers/{offer}/reservations` | Book an offer |

### POST /api/imports

**Request body:**
```json
{
  "supplier": "supplier-a",
  "external_import_id": "import-2026-09-01-001",
  "sent_at": "2026-09-01T10:00:00Z",
  "offers": [
    {
      "external_id": "offer-a-10001",
      "property": { "code": "BCN-0001", "name": "Apartment near Sagrada Familia", "city": "Barcelona" },
      "check_in": "2026-10-10",
      "check_out": "2026-10-15",
      "max_guests": 4,
      "price": 72500,
      "currency": "EUR",
      "available_units": 2,
      "expires_at": "2026-09-10T23:59:59Z"
    }
  ]
}
```

**Response — `202 Accepted`:**
```json
{ "data": { "id": 15, "status": "pending" } }
```

Offer processing happens asynchronously in `ProcessImportJob`, not in the request.

### GET /api/imports/{import}

```json
{
  "data": {
    "id": 15,
    "supplier": "supplier-a",
    "external_import_id": "import-2026-09-01-001",
    "sent_at": "2026-09-01T10:00:00Z",
    "status": "completed",
    "total_offers": 20,
    "processed_offers": 20,
    "error": null,
    "created_at": "2026-09-01T10:00:02Z",
    "completed_at": "2026-09-01T10:00:04Z"
  }
}
```

`status` is one of `pending`, `processing`, `completed`, `failed`.

### GET /api/properties

```
GET /api/properties?city=Barcelona&check_in=2026-10-10&check_out=2026-10-15&guests=2&page=1
```

```json
{
  "data": [
    {
      "code": "BCN-0001",
      "name": "Apartment near Sagrada Familia",
      "city": "Barcelona",
      "best_offer": {
        "id": 125,
        "supplier": "supplier-a",
        "price": 72500,
        "currency": "EUR",
        "available_units": 2,
        "expires_at": "2026-09-10T23:59:59Z"
      }
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 15, "total": 1 }
}
```

Filtering, cheapest-offer selection, sorting, and pagination all run in a single database query — see `SearchPropertiesAction`.

### POST /api/offers/{offer}/reservations

**Request body:**
```json
{
  "client_reference": "web-order-9f782b1c",
  "customer_name": "John Smith",
  "customer_email": "john@example.com"
}
```

**Response — `201 Created`:**
```json
{
  "data": {
    "id": 42,
    "offer_id": 125,
    "client_reference": "web-order-9f782b1c",
    "customer_name": "John Smith",
    "customer_email": "john@example.com",
    "status": "confirmed",
    "created_at": "2026-09-01T10:05:00Z"
  }
}
```

## Commands

All commands can also be run inside the Docker container:

```bash
docker compose exec app php artisan <command>
```

### Migrations & seeders

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

`SupplierSeeder` creates the two required suppliers (`supplier-a`, `supplier-b`) in every environment. The rest — sample properties, imports, offers, and reservations — only seed when `APP_ENV=local`.

### Queue worker

Import processing runs on a dedicated `imports` queue:

```bash
docker compose exec app php artisan queue:work --queue=imports
```

### Tests

Tests run against a real MySQL database (`{DB_DATABASE}_test`, e.g. `wtg_db_test`) instead of SQLite, so the suite exercises the same engine as production. Create it once:

```bash
docker compose exec db mysql -uroot -proot -e "
  CREATE DATABASE IF NOT EXISTS wtg_db_test;
  GRANT ALL PRIVILEGES ON wtg_db_test.* TO 'wtg_app'@'%';
"
```

Then run:

```bash
composer test                                    # Pest suite
composer test:types                               # type coverage (min 100%)

# Inside container
docker compose exec app php vendor/bin/pest
docker compose exec app php vendor/bin/pest --filter=ReservationApiTest
```

### Linting

```bash
composer lint        # fix code style with Laravel Pint
composer test:lint   # check only, do not modify files
```

### API documentation

```bash
composer doc         # regenerate OpenAPI spec
```

Then open **http://localhost/api/documentation**.

## Import idempotency

- `imports` has a unique index on `(supplier_id, external_import_id)`. `CreateImportAction` uses `createOrFirst()` and only dispatches `ProcessImportJob` when a new row was actually created — a repeated submission returns the existing import untouched, without re-queuing it.
- `ProcessImportJob` also implements `ShouldBeUnique`, keyed on a hash of `supplier_id:external_import_id`, as a second line of defense against two workers processing the same import concurrently.
- `offers` has a unique index on `(supplier_id, external_id)`. `ProcessImportAction` uses `updateOrCreate()`, so a later import for the same offer updates the existing row instead of creating a duplicate.

## Reservation safety

Two simultaneous bookings of the last unit are prevented by an atomic update inside a transaction:

```sql
UPDATE offers
SET available_units = available_units - 1
WHERE id = ?
  AND available_units > 0
  AND expires_at > CURRENT_TIMESTAMP
```

The affected row count is checked before the reservation is inserted — if no row was updated, the API returns `409 Conflict`. InnoDB locks the row for the duration of the transaction, so a second concurrent request re-evaluates `available_units > 0` against the already-decremented value and cannot also succeed.

`reservations.client_reference` is unique: resubmitting the same reference for the same offer returns the existing reservation instead of booking again; reusing it for a *different* offer returns `409 Conflict`.
