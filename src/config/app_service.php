<?php


return [
    "client_id" => env('APP_SERVICE_CLIENT_ID'),
    "secret_client" => env('APP_SERVICE_SECRET'),
    "accounting_base_url" => env('APP_SERVICE_ACCOUNTING_BASE_URL'),


    'routes' => [
        'web_prefix' => env('APP_SERVICE_WEB_PREFIX', '/admin'),
        'api_prefix' => env('APP_SERVICE_API_PREFIX', '/api/v1/admin'),
    ],

    'permissions' => [
        'services.list' => [
            'display_name' => 'List App Services',
            'description' => 'Permission to list all app services',
        ],
        'services.create' => [
            'display_name' => 'Create App Service',
            'description' => 'Permission to create a new app service',
        ],
        'services.update' => [
            'display_name' => 'Update App Service',
            'description' => 'Permission to update an existing app service',
        ],
        'services.delete' => [
            'display_name' => 'Delete App Service',
            'description' => 'Permission to delete an app service',
        ],
    ],

    'extra_fields' => [
        // Example: 'content.product' or 'app-service::product',
    ]
];
