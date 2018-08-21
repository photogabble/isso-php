<?php

if(! defined('APP_ROOT')) {
    define('APP_ROOT', realpath(__DIR__ . '/../'));
}

include APP_ROOT . '/vendor/autoload.php';

$app = new \Photogabble\Tuppence\App();

try {
    $app->setExceptionHandler(new \App\Exceptions\Handler($app));
} catch (\Photogabble\Tuppence\ErrorHandlers\InvalidHandlerException $e) {
    echo $e->getMessage();
    die();
}

//
// Config
//
$app->getContainer()->share('config', new \Adbar\Dot(include __DIR__ . '/../config.php'));

//
// Services
//
$app->register(new \App\Services\Routes());
$app->register(new \App\Services\Database());
$app->register(new \App\Services\Console());

return $app;