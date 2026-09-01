# Esanj App Service

The **App Service** package is part of the Esanj microservice ecosystem. It lets a host application register
external **services** (each identified by a `client_id`), assign **per‑service API permissions**, and validate
incoming **JWT** requests from those services — through both a web admin panel and a JSON API.

**Supports:** Laravel 10 · 11 · 12 · 13 — PHP 8.2+

---

## 🧩 Features

- Register, list, update, soft‑delete, and restore services (web UI **and** API).
- Per‑service API permissions, importable from config.
- `service.validation` middleware — validates a service's JWT (RS256) and attaches its identity to the request.
- `service.permission:{key}` middleware — authorizes a service for a specific permission.
- API‑only mode for projects that don't need the UI.
- Integrates with **esanj/managers** (admin auth & permissions) and **esanj/layout-master** (UI).

---

## ⚙️ Requirements

- **PHP** 8.2+
- **Laravel** 10–13
- **`esanj/managers`** — admin authentication & the `manager.auth` / `manager.permission` middleware (required).
- **`esanj/auth-bridge`** — OAuth client credentials & the public key used to verify service JWTs (required;
  installed automatically).
- **`esanj/layout-master`** — only for the **web UI** (the views extend its master layout). Not needed in
  [API‑only mode](#-api-only-mode).

---

## 🚀 Installation

```bash
composer require esanj/app-service
php artisan app-service:install
```

`app-service:install` will:

1. Publish the **assets** and **config** (`config/esanj/app_service.php`).
2. Ask to run **migrations** (creates `services`, `service_metas`, `service_permissions`,
   `service_permission_map`).
3. Import the **service permissions** (`app-service:permissions-import`).
4. Import the **manager permissions** (the `permissions` from this package's config) into `esanj/managers`.

> Steps 3–4 are skipped with a warning if the relevant tables don't exist yet — run `php artisan migrate` first,
> then re‑run the import commands.

---

## 🔧 Configuration

The config file is `config/esanj/app_service.php` (merged under the key `esanj.app_service`).

| Key                   | Default                       | Env                       | Description                                            |
|-----------------------|-------------------------------|---------------------------|--------------------------------------------------------|
| `routes.web_prefix`   | `admin`                       | `APP_SERVICE_WEB_PREFIX`  | Prefix for the web panel routes.                       |
| `routes.api_prefix`   | `api`                         | `APP_SERVICE_API_PREFIX`  | Prefix for the API routes.                             |
| `just_api`            | `false`                       | `APP_SERVICE_JUST_API`    | API‑only mode (no web panel/views).                    |
| `middlewares.web`     | `['web', 'manager.auth:web']` | —                         | Middleware for the web panel.                          |
| `middlewares.api`     | `['api', 'manager.auth:api']` | —                         | Middleware for the management API.                     |
| `permissions`         | 4 `services.*` permissions    | —                         | **Manager** permissions guarding the panel/management API. |
| `service_permissions` | 5 `transactions.*` examples   | —                         | **Service** permissions assignable to services.        |
| `access_provider`     | action → permission map       | —                         | Which manager permission each controller action needs. |
| `extra_fields`        | `[]`                          | —                         | Blade views injected into the create/edit forms.       |

> ℹ️ **Two kinds of permissions** — don't confuse them:
> - `permissions` (`services.list/create/update/delete`) gate **managers** using the admin panel/management API.
> - `service_permissions` (`transactions.*`, etc.) are granted to **services** and checked by
>   `service.permission`.

---

## 🖥️ Web UI

Available when `just_api` is `false`. Visit `route('services.index')` (default `/admin/services`) to list, create,
edit, delete, and restore services.

| Method      | URI (default)                | Route name           | Manager permission   |
|-------------|------------------------------|----------------------|----------------------|
| GET         | `/admin/services`            | `services.index`     | `services.list`      |
| GET         | `/admin/services/create`     | `services.create`    | `services.create`    |
| POST        | `/admin/services`            | `services.store`     | `services.create`    |
| GET         | `/admin/services/{id}/edit`  | `services.edit`      | `services.update`    |
| PUT/PATCH   | `/admin/services/{id}`       | `services.update`    | `services.update`    |
| DELETE      | `/admin/services/{id}`       | `services.destroy`   | `services.delete`    |
| POST        | `/admin/services/{id}/restore` | `services.restore` | `services.delete`    |
| GET         | `/admin/services/validation` | `services.validation`| (authenticated manager) |

---

## 🔗 Management API

These endpoints **manage** service records and are guarded by `manager.auth:api` (a manager Bearer token from
`esanj/managers`) plus the matching `services.*` manager permission.

| Method   | URI (default)                 | Route name              | Manager permission |
|----------|-------------------------------|-------------------------|--------------------|
| GET      | `/api/services`               | `api.services.index`    | `services.list`    |
| POST     | `/api/services`               | `api.services.store`    | `services.create`  |
| GET      | `/api/services/{id}`          | `api.services.show`     | `services.list`    |
| PUT/PATCH| `/api/services/{id}`          | `api.services.update`   | `services.update`  |
| DELETE   | `/api/services/{id}`          | `api.services.destroy`  | `services.delete`  |
| POST     | `/api/services/{id}/restore`  | `api.services.restore`  | `services.delete`  |
| GET      | `/api/services/validation`    | `api.services.validation` | (manager auth)   |

**Body for create/update:** `name` (required, unique), `client_id` (required, unique), `is_active` (boolean),
`extra` (object, stored as JSON), `permissions` (array of **`service_permissions` IDs**).

---

## 🔐 Service permissions

Define the permissions your services can hold in `config/esanj/app_service.php`:

```php
'service_permissions' => [
    'transactions.list' => [
        'display_name' => 'List Transactions',
        'description'  => 'Allows viewing the list of all transactions for the service',
    ],
    // ...
],
```

Import them into the database:

```bash
php artisan app-service:permissions-import
```

Then assign them to a service from the edit page, or via the API `permissions` array.

---

## 🧩 Middleware

### `service.validation` — validate a service JWT

Validates the `Authorization: Bearer {token}` JWT (RS256, verified with the auth‑bridge public key). On success it
attaches to the request:

- `jwt_client_id` — the token's `aud` (audience) claim.
- `jwt_payload` — the full decoded payload.

On a missing/invalid/expired token it throws a `401 Unauthorized`.

### `service.permission:{key}` — authorize a service

Checks that the calling service (identified from its JWT) is **active** and holds the given permission. **It
validates the JWT itself**, so you don't have to chain `service.validation` before it.

```php
// service.permission already validates the token, so this is enough:
Route::get('/transactions', [TransactionController::class, 'index'])
    ->middleware('service.permission:transactions.list');

// Add service.validation only if you also need $request->attributes 'jwt_payload' in the handler:
Route::get('/transactions', [TransactionController::class, 'index'])
    ->middleware(['service.validation', 'service.permission:transactions.list']);
```

---

## 🌐 API‑only mode

If you don't need the web panel:

```env
APP_SERVICE_JUST_API=true
```

This disables the web routes and views; the API stays fully functional. In this mode `esanj/layout-master` is not
required.

---

## 🎛️ Extending the forms

Inject your own Blade views into the create/edit forms via config (each is `@include`‑d):

```php
'extra_fields' => [
    'content.product',
],
```

---

## 📚 Documentation

For a complete, beginner‑friendly, step‑by‑step walkthrough — registering a service, assigning permissions,
protecting your own service API, adding a custom permission or form field, and troubleshooting — see
**[docs/GUIDE.md](docs/GUIDE.md)**.

---

## 🪪 License

MIT — part of the Esanj ecosystem.