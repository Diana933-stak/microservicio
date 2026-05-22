<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && ($_SERVER['REQUEST_URI'] ?? '/') === '/') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'message' => 'Microservicio Registro de Retrospectivas Scrum',
        'sprints' => 'http://127.0.0.1:8000/index.php/sprints',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$cors = require __DIR__ . '/../app/Presentation/Middlewares/CorsMiddleware.php';
$endpoints = require __DIR__ . '/../app/Presentation/Routers/endpoints.php';

$app = AppFactory::create();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

if ($scriptName === '/' || $scriptName === '') {
    $app->setBasePath('');
} elseif (str_starts_with($requestUri, $scriptName . '/')) {
    $app->setBasePath($scriptName);
} else {
    $basePath = rtrim(str_replace('/public', '', dirname($scriptName)), '/');
    $app->setBasePath($basePath === '/' ? '' : $basePath);
}

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$cors($app);
$endpoints($app);

$app->run();
