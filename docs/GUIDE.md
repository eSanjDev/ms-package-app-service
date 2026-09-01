# 📚 Esanj App Service — Complete Beginner's Guide

This guide assumes **you have never used this package before**. It explains everything in plain language, step by
step, with copy‑paste examples. If you can edit a config file and add a route, you can follow along.

> 💡 **What is this package for?** Imagine your application is a microservice that other services call over HTTP.
> This package lets you (1) **register those other services** (each has a `client_id`), (2) **grant each service
> specific permissions**, and (3) **protect your own API** so only registered, authorized services can call it —
> by checking the JWT they send. It comes with an admin panel and a management API to do all of this.

---

## Table of Contents

1. [The big picture](#1-the-big-picture)
2. [Two kinds of permissions (read this first)](#2-two-kinds-of-permissions-read-this-first)
3. [Requirements & dependencies](#3-requirements--dependencies)
4. [Installation, step by step](#4-installation-step-by-step)
5. [Configuration & `.env`](#5-configuration--env)
6. [Registering a service in the panel](#6-registering-a-service-in-the-panel)
7. [Registering a service via the API](#7-registering-a-service-via-the-api)
8. [Protecting *your* API for services to call](#8-protecting-your-api-for-services-to-call)
9. [How JWT validation works](#9-how-jwt-validation-works)
10. [Recipe: add a new service permission](#10-recipe-add-a-new-service-permission)
11. [Recipe: protect a new endpoint](#11-recipe-protect-a-new-endpoint)
12. [Recipe: add a custom field to the form](#12-recipe-add-a-custom-field-to-the-form)
13. [Recipe: store extra data per service (meta)](#13-recipe-store-extra-data-per-service-meta)
14. [Recipe: API‑only mode](#14-recipe-api-only-mode)
15. [Configuration reference](#15-configuration-reference)
16. [Route reference](#16-route-reference)
17. [Troubleshooting](#17-troubleshooting)
18. [Cheat sheet](#18-cheat-sheet)

---

## 1. The big picture

There are **two audiences** in this package — keep them straight and everything else is easy:

| Audience    | Who they are                              | How they authenticate                  | What they do                                   |
|-------------|-------------------------------------------|----------------------------------------|------------------------------------------------|
| **Managers**| Your human admins                         | `esanj/managers` (login / Bearer token) | Use the panel/management API to register services. |
| **Services**| Other microservices calling your app      | A **JWT** (`Authorization: Bearer ...`) | Call your protected API endpoints.             |

So the flow is:
1. A **manager** registers a service and ticks which permissions it gets.
2. That **service** later calls your API, sending its JWT.
3. The `service.permission` middleware checks the JWT, finds the matching service, and confirms it has the
   permission — then lets the request through.

---

## 2. Two kinds of permissions (read this first)

The single most common source of confusion. There are **two separate permission lists** in the config:

| Config key            | Example keys                     | Guards…                          | Checked by               |
|-----------------------|----------------------------------|----------------------------------|--------------------------|
| `permissions`         | `services.list`, `services.create` | **Managers** in the admin panel | `manager.permission` (from `esanj/managers`) |
| `service_permissions` | `transactions.list`, `transactions.create` | **Services** calling your API | `service.permission`     |

- `permissions` decide *which admins may manage services*.
- `service_permissions` decide *what each registered service is allowed to do*.

They live in the same file but are imported by different commands into different tables. Whenever you read
"permission" below, check which of these two is meant.

---

## 3. Requirements & dependencies

- **PHP** 8.2+, **Laravel** 10–13.
- **`esanj/managers`** (required) — provides admin login and the `manager.auth` / `manager.permission` middleware.
- **`esanj/auth-bridge`** (required, auto‑installed) — provides OAuth client credentials and the **public key**
  used to verify service JWTs.
- **`esanj/layout-master`** (only for the web UI) — the panel views extend its master layout. In
  [API‑only mode](#14-recipe-api-only-mode) you don't need it.

---

## 4. Installation, step by step

**Step 1 — require the package:**

```bash
composer require esanj/app-service
```

**Step 2 — run the installer:**

```bash
php artisan app-service:install
```

It publishes the config + assets, offers to run migrations, then imports both permission lists. If it warns that a
table is missing, run `php artisan migrate` first, then:

```bash
php artisan app-service:permissions-import   # service_permissions → DB
php artisan manager:permissions-import        # manager permissions → DB (from esanj/managers)
```

**Step 3 — make sure the JWT public key exists** (see [section 9](#9-how-jwt-validation-works)).

---

## 5. Configuration & `.env`

The config file is `config/esanj/app_service.php`. Common `.env` values:

```env
APP_SERVICE_WEB_PREFIX=admin      # panel URL prefix (default: admin)
APP_SERVICE_API_PREFIX=api        # API URL prefix (default: api)
APP_SERVICE_JUST_API=false        # true = no web panel
```

The JWT/OAuth bits come from `esanj/auth-bridge`'s config (`esanj.auth_bridge.*`): `base_url`, `client_id`,
`client_secret`, and `public_key_path`.

> ⚠️ After any config/`.env` change, run `php artisan config:clear`.

---

## 6. Registering a service in the panel

1. Log in as a manager (via `esanj/managers`).
2. Go to `route('services.index')` — by default `/admin/services`.
3. Click **Add New Service**, then fill in:
   - **Name** — a label for the service (must be unique).
   - **Client ID** — the service's OAuth client id (must be unique). The **Validate** button looks it up on the
     auth‑bridge server so you can confirm it's real.
   - **Status** — Active/Deactive. Inactive services are rejected by `service.permission`.
   - **Permissions** — tick the `service_permissions` this service may use.
4. Save. You can edit, soft‑delete, and restore services later from the same screen.

---

## 7. Registering a service via the API

The **management API** is for your own admin tools. Authenticate with a **manager** Bearer token (from
`esanj/managers`) — *not* a service JWT.

```http
POST /api/services
Authorization: Bearer {manager-access-token}
Content-Type: application/json

{
    "name": "Billing Service",
    "client_id": "billing-abc123",
    "is_active": true,
    "permissions": [3, 5]          // service_permissions IDs
}
```

> 📌 `permissions` is an array of **`service_permissions` table IDs** (integers), the same values the panel's
> checkboxes use — not the permission key strings.

Other management endpoints: `GET /api/services`, `GET /api/services/{id}`, `PUT /api/services/{id}`,
`DELETE /api/services/{id}`, `POST /api/services/{id}/restore`.

---

## 8. Protecting *your* API for services to call

This is the core purpose. Put `service.permission:{key}` on the endpoints other services will call:

```php
use Illuminate\Support\Facades\Route;

Route::get('/transactions', [TransactionController::class, 'index'])
    ->middleware('service.permission:transactions.list');
```

What happens on each request:
1. The middleware reads the `Authorization: Bearer {jwt}` header and **validates the JWT**.
2. It finds the service whose `client_id` matches the token's audience (`aud`).
3. It confirms the service is **active** and **has** `transactions.list`.
4. If all good, the request proceeds; the matched `Service` model is attached as the request attribute `service`.
   Otherwise it returns `401` (bad token) or `403` (unknown/inactive/unauthorized service).

> ✅ `service.permission` **already validates the token**, so you don't need to also add `service.validation`.
> Add `service.validation` only when your controller needs the decoded payload (`$request->attributes->get(
> 'jwt_payload')`) or the client id (`jwt_client_id`).

Reading the service in your controller:

```php
public function index(Request $request)
{
    $service = $request->attributes->get('service');   // the calling Service model
    // ...
}
```

---

## 9. How JWT validation works

- Services send a JWT signed with **RS256**.
- This package verifies it using a **public key** at `config('esanj.auth_bridge.public_key_path')`
  (default: `storage_path('oauth-public.key')`).
- The token's **`aud`** (audience) claim must equal a registered service's `client_id`.

So for validation to work, that public key file must exist on your server. If it's missing, requests fail with a
generic `500` and *"Public key file not found"* (with the path) appears in the logs.

| Situation                          | Response |
|------------------------------------|----------|
| No `Authorization` header          | `401`    |
| Malformed / wrong‑signature token  | `401`    |
| Expired token                      | `401`    |
| Token OK but service unknown       | `403`    |
| Service inactive                   | `403`    |
| Service lacks the permission       | `403`    |

---

## 10. Recipe: add a new service permission

Say your service area is "reports" and you want a `reports.export` permission.

**Step 1 — add it** to `config/esanj/app_service.php` under `service_permissions`:

```php
'service_permissions' => [
    // ...existing...
    'reports.export' => [
        'display_name' => 'Export Reports',
        'description'  => 'Allows the service to export reports',
    ],
],
```

**Step 2 — import it:**

```bash
php artisan app-service:permissions-import
```

**Step 3 — assign it** to a service (tick it on the edit page, or include its ID in the API `permissions` array).

**Step 4 — protect the endpoint** with `service.permission:reports.export`.

> The panel groups permissions by the text before the first dot (`reports.*` → a "reports" group), so related
> permissions appear together automatically.

---

## 11. Recipe: protect a new endpoint

```php
// routes/api.php (your app)
Route::middleware('service.permission:reports.export')->group(function () {
    Route::get('/reports/export', [ReportController::class, 'export']);
});
```

Only active services that hold `reports.export` can reach it. Everything else gets `401`/`403` automatically.

---

## 12. Recipe: add a custom field to the form

You can inject your own Blade views into the create/edit forms without editing the package views.

**Step 1 — create a partial**, e.g. `resources/views/admin/service-extra.blade.php`. On the **edit** form the
current `$service` is in scope:

```blade
<div class="card p-6 mt-4">
    <h3>Extra settings</h3>
    {{-- your inputs; they post to the same form --}}
    <input type="text" name="webhook_url" value="{{ old('webhook_url', $service->getMeta('webhook_url')) }}">
</div>
```

**Step 2 — register it** in `config/esanj/app_service.php`:

```php
'extra_fields' => [
    'admin.service-extra',
],
```

> The package validates only its own fields. To persist your custom inputs, handle them in your own code (e.g. a
> model observer or a small controller) — a natural place is the [service meta](#13-recipe-store-extra-data-per-service-meta).

---

## 13. Recipe: store extra data per service (meta)

Each service supports arbitrary key/value metadata (stored as JSON):

```php
$service->setMeta('webhook_url', 'https://billing.example.com/hooks');
$service->getMeta('webhook_url');          // → the value, or null
$service->getMeta('rate_limit', 100);      // → value or the default 100
$service->deleteMeta('webhook_url');
```

This is the recommended place to keep custom per‑service settings added via `extra_fields`.

---

## 14. Recipe: API‑only mode

If your project has no admin UI:

```env
APP_SERVICE_JUST_API=true
```

This stops the web routes and views from loading; the management API and the `service.*` middleware keep working.
In this mode you don't need `esanj/layout-master`.

---

## 15. Configuration reference

File: `config/esanj/app_service.php` (key `esanj.app_service`).

| Key                   | Default                       | Meaning                                                       |
|-----------------------|-------------------------------|---------------------------------------------------------------|
| `routes.web_prefix`   | `admin`                       | Web panel URL prefix.                                         |
| `routes.api_prefix`   | `api`                         | Management API URL prefix.                                    |
| `just_api`            | `false`                       | Disable the web panel.                                        |
| `middlewares.web`     | `['web','manager.auth:web']`  | Web panel middleware.                                         |
| `middlewares.api`     | `['api','manager.auth:api']`  | Management API middleware.                                    |
| `permissions`         | `services.*`                  | **Manager** permissions for the panel/management API.        |
| `service_permissions` | `transactions.*`              | **Service** permissions assignable to services.              |
| `access_provider`     | action → permission map       | Manager permission required by each controller action.       |
| `extra_fields`        | `[]`                          | Blade views injected into the create/edit forms.             |

---

## 16. Route reference

**Web panel** (when `just_api = false`, prefix `admin`): `services.index`, `services.create`, `services.store`,
`services.edit`, `services.update`, `services.destroy`, `services.restore`, `services.validation`.

**Management API** (prefix `api`): `api.services.index/store/show/update/destroy`, `api.services.restore`,
`api.services.validation`.

**Your own endpoints**: anything you protect with `service.permission:{key}` (and optionally `service.validation`).

---

## 17. Troubleshooting

**`500` "Service authentication is not configured correctly." (log: "Public key file not found").**
The RS256 public key isn't where the package expects. Place it at `storage_path('oauth-public.key')` or set
`esanj.auth_bridge.public_key_path` to the correct path, then `php artisan config:clear`.

**A service gets `403` even though I ticked its permission.**
Check three things: the service is **Active**, the permission was **imported** (`app-service:permissions-import`),
and the JWT's `aud` exactly matches the service's `client_id`.

**Managers can't open the panel (`403`).**
The manager lacks a `services.*` permission. Import them (`manager:permissions-import`) and grant them to the
manager in `esanj/managers` (admins bypass all checks).

**The panel pages error / look broken.**
The UI needs `esanj/layout-master` installed and its assets built. Install it, or switch to API‑only mode.

**API create/update ignores my `permissions`.**
Send permission **IDs** (integers from the `service_permissions` table), not key strings — they must pass
`exists:service_permissions,id`.

**`service_permissions` table doesn't exist.**
Run `php artisan migrate`, then `php artisan app-service:permissions-import`.

**Config changes don't apply.**
`php artisan config:clear` (and `config:cache` again if you cache config in production).

---

## 18. Cheat sheet

```bash
# Install & set up
composer require esanj/app-service
php artisan app-service:install
php artisan app-service:permissions-import   # re-import service permissions after editing config

# After config/.env changes
php artisan config:clear
```

```php
// Protect your API so only authorized services can call it
Route::get('/transactions', [TransactionController::class, 'index'])
    ->middleware('service.permission:transactions.list');

// Read the calling service / token in the controller
$service = $request->attributes->get('service');
$payload = $request->attributes->get('jwt_payload');   // needs service.validation

// Per-service metadata
$service->setMeta('key', 'value');
$service->getMeta('key', $default);
```

| I want to...                          | Do this                                                              |
|---------------------------------------|---------------------------------------------------------------------|
| Register a service                    | Panel `route('services.index')` → Add New, or `POST /api/services`   |
| Add a service permission              | edit config `service_permissions` → `app-service:permissions-import` |
| Protect an endpoint for services      | `service.permission:{key}` middleware                                |
| Get the decoded token in a handler    | add `service.validation`, read `jwt_payload` / `jwt_client_id`       |
| Add a field to the form               | `extra_fields` config → a Blade partial                              |
| Store custom per‑service data         | `$service->setMeta()` / `getMeta()`                                  |
| Run without the UI                    | `APP_SERVICE_JUST_API=true`                                          |

---

Need the quick reference instead? See the [README](../README.md).