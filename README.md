# Esanj App Service

The **App Service** package is part of the Esanj microservice ecosystem.  
It provides centralized management, permission handling, and connectivity between different services in your
Laravel-based projects.

---

## 🧩 Features

- Service registration, listing, updating, and deletion via UI or API.
- Permission management per service.
- Extendable configuration to add custom fields and permissions.
- Built-in middleware for permission checks.
- Works seamlessly with **esanj/managers** and **esanj/layout-master** packages.

---

## ⚙️ Requirements

- **PHP**: >= 8.2
- **Laravel**: >= 12.x
- Dependencies:
    - `esanj/managers`
    - `esanj/layout-master`

---

## 🚀 Installation

Run the following commands in your Laravel project:

#### Step 1:

```bash
composer require esanj/app-service
```

#### Step 2:

```bash
php artisan app-service:install
```

The install command will automatically publish static assets and configuration files.

---

## 🖥️ UI Usage

After installation, you can access the service management interface via:

Route name: ```route('services.index')```

Direct URL: ```/{web_prefix}/services```

From this interface, you can:

* View the list of services
* Create new services
* Edit existing services
* Delete services

## 🔗 API Endpoints

The package also exposes a set of RESTful API endpoints for managing services.

All API requests must include a valid token, which can be obtained via the Manager package.

| Method     | URI                       | Description / Behavior     |
|:-----------|:--------------------------|:---------------------------|
| **GET**    | `/{prefix}/services`      | List all services          |
| **POST**   | `/{prefix}/services`      | Create a new service       |
| **PUT**    | `/{prefix}/services/{id}` | Update an existing service |
| **DELETE** | `/{prefix}/services/{id}` | Delete a service           |

---

## 🧠 Configuration

You can extend the service edit page by adding extra fields.

Simply include the path to your custom Blade components inside the configuration file:

```php
'extra_fields' => [
    'page.contents.price.',
],
```

## 🔐 Service Permissions

Define service-specific permissions in your configuration file as follows:

```php
'service_permissions' => [
     'transactions.list' => [
         'display_name' => 'List Transactions',
         'description'  => 'Allows viewing the list of all transactions for the service',
     ],
     'transactions.view' => [
         'display_name' => 'View Transaction Details',
         'description'  => 'Allows viewing detailed information of a single transaction',
     ],
     'transactions.create' => [
         'display_name' => 'Create Transaction',
         'description'  => 'Allows creating new transactions within the service',
     ],
     'transactions.update' => [
         'display_name' => 'Update Transaction',
         'description'  => 'Allows updating existing transaction information for the service',
     ],
     'transactions.delete' => [
        'display_name' => 'Delete Transaction',
        'description'  => 'Allows deleting transactions from the service',
     ],
],
```

Then, run the following command to import permissions into the database:

```bash
php artisan app-service:permissions-import
```

## ⚡ Middleware Permission Check

To check a service’s permissions, use the built-in middleware:

```php
service.permission:{permission}
```

If the service does not have the required permission, an appropriate error response will be returned.

Example:

```php
Route::get('/transactions', [TransactionController::class, 'index'])->middleware('service.permission:transactions.list');
```

## 🌐 API-Only Mode
If your project only uses APIs and has no need for the UI, you can enable API-only mode by setting:

```php
'just_api' => env('APP_SERVICE_JUST_API', false)
```
This disables all UI routes while keeping the full API functionality intact.

## 🪪 License
This package is part of the Esanj ecosystem and is intended for internal or authorized use within Esanj-based projects.
