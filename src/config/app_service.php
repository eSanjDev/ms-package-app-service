<?php


return [
    'routes' => [
        'web_prefix' => env('APP_SERVICE_WEB_PREFIX', '/admin'),

    ],

    'permissions' => [
        'app_services.list' => [
            'display_name' => 'List App Services',
            'description' => 'Permission to list all app services',
        ],
        'app_services.create' => [
            'display_name' => 'Create App Service',
            'description' => 'Permission to create a new app service',
        ],
        'app_services.update' => [
            'display_name' => 'Update App Service',
            'description' => 'Permission to update an existing app service',
        ],
        'app_services.delete' => [
            'display_name' => 'Delete App Service',
            'description' => 'Permission to delete an app service',
        ],
    ],
];
