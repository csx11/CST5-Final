<?php
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $m) || preg_match("/^'(.*)'$/", $value, $m)) {
            $value = $m[1];
        }
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnv(dirname(__DIR__) . '/.env');

define('DB_HOST',     $_ENV['DB_HOST']         ?? $_ENV['MYSQLHOST']     ?? 'localhost');
define('DB_USERNAME', $_ENV['DB_USERNAME']      ?? $_ENV['MYSQLUSER']     ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD']      ?? $_ENV['MYSQLPASSWORD'] ?? '');
define('DB_NAME',     $_ENV['DB_NAME']          ?? $_ENV['MYSQLDATABASE'] ?? 'railway');
define('DB_PORT',     (int)($_ENV['DB_PORT']    ?? $_ENV['MYSQLPORT']     ?? 3306));

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    http_response_code(500);
    die('<h2 style="font-family:sans-serif;color:#dc2626;padding:2rem;">
            Database connection failed: ' . htmlspecialchars($conn->connect_error) . '
         </h2>');
}

$conn->set_charset('utf8mb4');
