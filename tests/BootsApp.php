<?php

namespace App\Tests;

use Photogabble\Tuppence\App;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class BootsApp extends TestCase
{
    /** @var App */
    protected $app;

    /** @var TestEmitter */
    protected $emitter;

    /**
     * @var ResponseInterface|null
     */
    protected $lastRequest;

    protected function bootApp()
    {
        $e = new TestEmitter();
        $this->emitter = $e;
        $this->app = include __DIR__ . '/../src/bootstrap.php';
        $this->app->getContainer()->share('emitter', function () use ($e) {
            return $e;
        });
    }

    /**
     * @param ServerRequest $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function runRequest(ServerRequest $request)
    {
        $this->bootApp();
        $this->app->run($request);
        $this->lastRequest = $this->emitter->getResponse();
        return $this->lastRequest;
    }

    protected function assertResponseOk()
    {
        $this->assertFalse(is_null($this->lastRequest));
        $this->assertEquals(200, $this->lastRequest->getStatusCode());
    }

    protected function assertJsonResponse()
    {
        $this->assertInstanceOf(JsonResponse::class, $this->lastRequest);
    }

    protected function assertJsonResponseValueEquals($key, $expected)
    {
        $decoded = json_decode($this->lastRequest->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE){
            $this->fail(json_last_error_msg());
        }

        $this->assertArrayHasKey($key, $decoded);
        $this->assertEquals($expected, $decoded[$key]);
    }
}