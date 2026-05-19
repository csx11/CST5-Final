<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$base = __DIR__;

if ($uri !== '/' && file_exists($base . $uri) && !is_dir($base . $uri)) {
    return false;
}

if ($uri === '/' || $uri === '/index.php') {
    require $base . '/index.php';
    return true;
}

$file = $base . $uri;
if (file_exists($file) && !is_dir($file)) {
    require $file;
    return true;
}

http_response_code(404);
echo '<h1 style="font-family:sans-serif">404 &mdash; Page not found</h1>';
