<?php
declare(strict_types=1);

$config = [
    'app_name' => getenv('APP_NAME') ?: 'ResellNom Domain Platform',
    'app_env' => getenv('APP_ENV') ?: 'production',
    'app_url' => rtrim(getenv('APP_URL') ?: 'https://panel.resellnom.com', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Dhaka',
    'security' => [
        'session_name' => getenv('SESSION_NAME') ?: 'resellnom_session',
        'csrf_ttl' => 3600,
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'domainpanel_rn',
        'user' => getenv('DB_USER') ?: 'domainpanel_rn',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $local = require $localConfig;
    if (is_array($local)) {
        $config = array_replace_recursive($config, $local);
    }
}

return $config;
