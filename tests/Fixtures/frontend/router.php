<?php

declare(strict_types=1);

$path = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/') {
    echo 'frontend ready';

    return;
}

if ($path === '/environment') {
    header('Content-Type: application/json');
    echo json_encode(['backend' => getenv('FIXTURE_BACKEND_URL'), 'url' => getenv('FIXTURE_FRONTEND_URL')]);

    return;
}

if (str_starts_with($path, '/api/')) {
    $context = stream_context_create(['http' => [
        'header' => 'X-Forwarded-Host: '.$_SERVER['HTTP_HOST']."\r\n",
        'ignore_errors' => true,
    ]]);

    echo file_get_contents(getenv('FIXTURE_BACKEND_URL').mb_substr($path, 4), false, $context);

    return;
}

http_response_code(404);
