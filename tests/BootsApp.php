<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use GuzzleHttp\ClientInterface;
use Photogabble\Tuppence\App;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;
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
        $this->app->getContainer()->extend(ClientInterface::class)->setConcrete(function(){
            $stub = $this->createMock(ClientInterface::class);
            $stub->method('request')
                ->willReturn(new HtmlResponse('<html><head><title>PHP-ISSO Unit Test</title></head><body id="isso-thread" data-title="Foo!">Hello World!</body></html>'));
            return $stub;
        });
    }

    protected function setUp()
    {
        $this->bootApp();
        $this->runDatabaseMigrations();
    }

    protected function runDatabaseMigrations()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->app->getContainer()->get(EntityManagerInterface::class);
        $tool = new SchemaTool($em);
        $tool->dropDatabase();

        try {
            $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
        } catch (ToolsException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @deprecated rename runServerRequest
     * @param ServerRequest $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function runRequest(ServerRequest $request)
    {
        $this->decodedJson = null;
        //$this->bootApp();
        $this->app->run($request);
        $this->lastRequest = $this->emitter->getResponse();
        return $this->lastRequest;
    }

    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function runServerRequest(ServerRequest $request): ResponseInterface
    {
        $this->decodedJson = $this->lastRequest = null;
        $this->app->run($request);
        $this->lastRequest = $this->emitter->getResponse();
        return $this->lastRequest;
    }

    /**
     * @todo rename to runRequest
     * @param string $method
     * @param string $uri
     * @param array $queryParams
     * @param array $headers
     * @param array $cookies
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function makeRequest(string $method, string $uri, array $queryParams = [], array $headers = [], array $cookies = []): ResponseInterface
    {
        $headers = array_merge([
            'Referer' => 'http://dev.local'
        ], $headers);

        // @todo set Referer header for testing environment

        $parts = parse_url($uri);
        $parts['query'] = (isset($parts['query']) ? $parts['query'] : '');
        parse_str($parts['query'], $query);
        $queryParams = array_merge($query, $queryParams);
        $request = new ServerRequest([], [], $parts['path'], $method, 'php://input', $headers, $cookies, $queryParams);

        return $this->runServerRequest($request);
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