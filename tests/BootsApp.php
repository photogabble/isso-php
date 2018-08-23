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

    /**
     * @var null|array
     */
    protected $decodedJson = null;

    protected function bootApp()
    {
        $e = new TestEmitter();
        $this->emitter = $e;
        $this->app = include __DIR__ . '/../src/bootstrap.php';
        $this->app->getContainer()->get('config')->set('database.path', ':memory:');

        $this->app->getContainer()->extend('emitter')->setConcrete(function () use ($e) {
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
        $this->decodedJson = null;
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

    protected function assertResponseStatusCodeEquals($code = 200)
    {
        $this->assertFalse(is_null($this->lastRequest));
        $this->assertEquals($code, $this->lastRequest->getStatusCode());
    }

    protected function assertJsonResponse()
    {
        $this->assertInstanceOf(JsonResponse::class, $this->lastRequest);
    }

    protected function assertJsonResponseValueEquals($key, $expected)
    {
        $this->assertEquals($expected, $this->getJsonResponseValue($key));
    }

    protected function assertJsonResponseHasKey($expected)
    {
        $this->assertArrayHasKey($expected, $this->getDecodedJsonResponse());
    }

    protected function getJsonResponseValue($key)
    {
        $decoded = $this->getDecodedJsonResponse();
        $this->assertArrayHasKey($key, $decoded);
        return $decoded[$key];
    }

    protected function assertJsonResponseEmpty()
    {
        $decoded = $this->getDecodedJsonResponse();
        $this->assertEquals([], $decoded);
    }

    protected function getDecodedJsonResponse()
    {
        if (!is_null($this->decodedJson)){
            return $this->decodedJson;
        }

        $decoded = json_decode($this->lastRequest->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->decodedJson = [];
            $this->fail(json_last_error_msg());
        }

        $this->decodedJson = $decoded;

        return $this->decodedJson;
    }
}