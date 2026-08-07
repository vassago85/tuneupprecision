# Tune Up — Long Range Precision Shooting

Training-primary website with a small gear shop and an admin dashboard, for
**Tune Up Long Range Precision Shooting** (client build for Charsley Digital).

> **Commit 1 — scaffold.** Schema, models, the payments/`MarkPaid` core, the
> public home page (built from the approved mockup), a themed Filament admin,
> and seeders. The public cart / checkout / booking flows and the payment
> gateway are **later commits**.

## Stack

- **Laravel 13**, PHP 8.5 (runs on 8.3+; drop to 8.4 if a host lags)
- **Livewire** (public interactive islands — added in a later commit)
- **Filament v5** admin panel at `/admin`, themed to the brand palette
- **PostgreSQL**
- **spatie/laravel-medialibrary** for product images
- Fonts: Saira Condensed (display), IBM Plex Sans (body), IBM Plex Mono (data)

## Architecture notes

- **Everything purchasable is a *payable*.** Both a course **Booking** and a
  shop **Order** have one polymorphic `payments` row and are confirmed through
  the single [`App\Actions\MarkPaid`](app/Actions/MarkPaid.php) action. In
  Phase 1 an admin clicks *Mark as paid*; in Phase 2 a gateway callback calls
  the exact same action — nothing downstream changes.
- **Money is integer cents (ZAR).** Display goes through
  [`App\Support\Money`](app/Support/Money.php); never floats.
- **Stock decrements on payment**, not on add-to-cart.
- **Booking seats** are reserved as a hold on creation and released by the
  `bookings:release-holds` command (scheduled every 15 min) when a pending
  hold expires.
- **Statuses are PHP enums** ([`app/Enums`](app/Enums)), never magic strings.
- **Product visibility:** out-of-stock products simply don't display (no "sold
  out"). **Fully-booked events DO display** as *Fully booked*.

## Local setup

Requires a running PostgreSQL and PHP 8.5 with `pdo_pgsql` + `gd`.

```bash
# 1. Install dependencies
composer install

# 2. Environment (already committed as .env for local; otherwise:)
cp .env.example .env
php artisan key:generate

# 3. Create the database (defaults to `tuneupprecision` on 127.0.0.1:5432)
#    Adjust DB_* and the ADMIN_* / EFT_* values in .env as needed.

# 4. Migrate + seed
php artisan migrate:fresh --seed

# 5. Build front-end assets (optional for the public page — its CSS is a
#    Blade partial — but required for Filament's own assets in production)
npm install && npm run build

# 6. Serve
php artisan serve
```

### Seeding

```bash
php artisan migrate:fresh --seed
```

Seeds: one admin user, 3 course templates (**Zero to First Steel**, **Applied
Long Range**, **Match-Ready**) each with published future events — including one
deliberately **full** event to exercise the *Fully booked* state — and 4
products, **one with `stock_qty = 0`** to prove the `available()` scope.

### Admin login

The seeder prints the credentials. Defaults (override via `.env`
`ADMIN_EMAIL` / `ADMIN_PASSWORD`):

- **URL:** `/admin`
- **Email:** `dirk@tuneupprecision.co.za`
- **Password:** `password`

## Tests

```bash
# Uses a dedicated `tuneupprecision_test` Postgres database (see phpunit.xml)
php artisan test
```

Covers: public home render (and the out-of-stock hide), admin dashboard +
resource pages render, and the `MarkPaid` money-flow (stock decrement, order
paid, booking confirmed + hold cleared, idempotency) and reference formats.

## Next commit (not built yet)

Public **shop listing + product detail** pages — a browsable grid of
`available()` products and a per-product page — reusing the existing
`x-shop.product-card` component and site layout. (The cart, guest checkout and
booking flows follow after that.)
