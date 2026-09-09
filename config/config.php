<?php
declare(strict_types=1);

return [
    'app_name' => getenv('APP_NAME') ?: 'ResellNom Domain Platform',
    'app_env' => getenv('APP_ENV') ?: 'production',
    'app_url' => rtrim(getenv('APP_URL') ?: '', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Dhaka',
    'security' => [
        'session_name' => getenv('SESSION_NAME') ?: 'resellnom_session',
        'csrf_ttl' => 3600,
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'resellnom',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];
