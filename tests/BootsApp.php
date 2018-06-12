<?php

namespace App\Tests;

use Photogabble\Tuppence\App;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class BootsApp extends TestCase
{
    /** @var App */
    protected $app;
    /** @var TestEmitter */
    protected $emitter;

    public function setUp()
    {
        $this->bootApp();
    }

    protected function bootApp()
    {
        $e = new TestEmitter();
        $this->emitter = $e;
        $this->app = include __DIR__ . '/../src/bootstrap.php';
        $this->app->getContainer()->share('emitter', function () use ($e) {
            return $e;
        });
    }

    protected function runRequest(ServerRequest $request)
    {
        $this->app->run($request);
        return (string)$this->emitter->getResponse()->getBody();
    }

    protected function assertResponseOk()
    {
        $this->assertEquals(200, $this->emitter->getResponse()->getStatusCode());
    }
}