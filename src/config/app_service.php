<?php


return [
    'routes' => [
        'web_prefix' => env('APP_SERVICE_WEB_PREFIX', 'admin'),
        'api_prefix' => env('APP_SERVICE_API_PREFIX', 'api'),
    ],

    'just_api' => env('APP_SERVICE_JUST_API', false),

    'middlewares' => [
        'api' => [
            'api',
            'manager.auth:api',
        ],
        'web' => [
            'web',
            'manager.auth:web',
        ],
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

    'service_permissions' => [
        'transactions.list' => [
            'display_name' => 'List Transactions',
            'description' => 'Allows viewing the list of all transactions for the service',
        ],

        'transactions.view' => [
            'display_name' => 'View Transaction Details',
            'description' => 'Allows viewing detailed information of a single transaction',
        ],

        'transactions.create' => [
            'display_name' => 'Create Transaction',
            'description' => 'Allows creating new transactions within the service',
        ],

        'transactions.update' => [
            'display_name' => 'Update Transaction',
            'description' => 'Allows updating existing transaction information for the service',
        ],

        'transactions.delete' => [
            'display_name' => 'Delete Transaction',
            'description' => 'Allows deleting transactions from the service',
        ],
    ],

    "access_provider" => [
        'list' => 'services.list',
        'store' => 'services.create',
        'update' => 'services.edit',
        'delete' => 'services.delete',
        'restore' => 'services.delete',
    ],

    'extra_fields' => [
        // Example: 'content.product' or 'app-service::product',
    ]
];
