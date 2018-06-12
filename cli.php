<?php

/** @var \Photogabble\Tuppence\App $app */
$app = include __DIR__ . '/src/bootstrap.php';

/** @var \Symfony\Component\Console\Application $cli */
$cli = $app->getContainer()->get(\Symfony\Component\Console\Application::class);

exit($cli->run());