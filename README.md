# 📡 Laravel App Service Package

This package helps you manage application-level services in a microservice architecture using Laravel. It lets users
define service names, client IDs, and manage metadata and access control easily via config & UI.

---

## ⚙️ Features

✅ Create and manage services from within your microservice  
✅ Validate service client IDs via Accounting microservice  
✅ Flexible service meta fields via config  
✅ Define route middlewares for custom access control  
✅ Built-in support for SOLID principles  
✅ Optional configuration and customization via config file

---

## 📦 Requirements

- PHP +8.2
- Laravel 10.0|11.0|12.0
- esanj/managers 0.3.x

---

## 🛠 Installation

You can install the package via Composer:

```bash
composer require esanjdev/ms-package-app-service
```

Then publish the config & migration files:

```bash
php artisan app-service:install
```

---

## ⚙️ Configuration

🔧 Update published config file:

```
config/esanj/app-service.php
```

    'permissions' => [
         'services.list' => [
            'display_name' => 'List App Services',
            'description' => 'Permission to list all app services',
        ],
    ],

    'middleware' => [
        'web' => ['web', ...],
        'api' => ['api', ...],
    ],

    'extra_fields' => [
        'content.product',
        ...
    ],

## 🌐 Usage

To access the user interface (UI) for managing services:

🔗 Use the named route services.index:

`route('services.index')`

or just visit this path on your app:

`/{web_prefix}/services`

From this panel you can:

- 🆕 Create new services
- 📡 Validate external client_id (via accounting microservice)
- 🗂 Manage dynamic metadata fields
- 📝 Edit or delete services
-

☝️ Make sure your middleware and permissions are properly configured in the config file.

## 📡 API Usage

Available endpoints:

Method Endpoint Description

- GET `/api/v1/admin/services` List services
- POST `/api/v1/admin/services` Create a new service
- PUT `/api/v1/admin/services/{id}` Update a service
- DELETE `/api/v1/admin/services/{id}` Delete a service

➡️ All routes are protected with middleware defined under config.

## 📜 License
MIT © eSanjDev
