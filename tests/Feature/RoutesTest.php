<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;
use Zend\Diactoros\ServerRequest;

class RoutesTest extends BootsApp
{
    public function testFetchRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], ['uri' => '/hello-world']));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"fetch"}', (string)$response->getBody());
    }

    public function testCountRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/count', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"count"}', (string)$response->getBody());
    }

    public function testCountsRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/count', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"counts"}', (string)$response->getBody());
    }

    public function testFeedRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/feed', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"feed"}', (string)$response->getBody());
    }

    public function testViewRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"view","id":9999}', (string)$response->getBody());
    }

    public function testEditRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999', 'PUT'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"edit","id":9999}', (string)$response->getBody());
    }

    public function testDeleteRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999', 'DELETE'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"delete","id":9999}', (string)$response->getBody());
    }

    public function testGetModerateRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/activate/abc123', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"getModerate","id":9999,"action":"activate","key":"abc123"}', (string)$response->getBody());
    }

    public function testPostModerateRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/activate/abc123', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"postModerate","id":9999,"action":"activate","key":"abc123"}', (string)$response->getBody());
    }

    public function testLikeRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/like', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"like","id":9999}', (string)$response->getBody());
    }

    public function testDislikeRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/dislike', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"dislike","id":9999}', (string)$response->getBody());
    }

    public function testDemoRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/demo', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"demo"}', (string)$response->getBody());
    }

    public function testPreviewRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/preview', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"preview"}', (string)$response->getBody());
    }

    public function testLoginRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/login', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"login"}', (string)$response->getBody());
    }

    public function testAdminRoute()
    {
        $this->markTestSkipped('@todo');
        $response = $this->runRequest(new ServerRequest([], [], '/admin', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"admin"}', (string)$response->getBody());
    }
}