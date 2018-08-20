<?php

namespace App\Tests\Feature;

use App\Http\Validation\Comment;
use App\Tests\BootsApp;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class RoutesTest extends BootsApp
{
    public function testFetchRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], ['uri' => '/hello-world']));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"fetch"}', (string)$response->getBody());
    }

    /**
     * Port of isso python testGet
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L53
     * @throws \Exception
     */
    public function testGet()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Lorem ipsum ...',
            'uri' => 'path'
        ]));
        $this->assertResponseOk();
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 1);

        $this->runRequest(new ServerRequest([], [], '/id/1', 'GET'));
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 1);
        $this->assertJsonResponseValueEquals('text', '<p>Lorem ipsum ...</p>');
    }

    /**
     * Port of isso python testCreate
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L65
     * @throws \Exception
     */
    public function testCreate()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Lorem ipsum ...',
            'uri' => 'path'
        ]));
        $this->assertResponseOk();
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('mode', 1);
        $this->assertJsonResponseValueEquals('text', '<p>Lorem ipsum ...</p>');
    }

    /**
     * Port of isso python textCreateWithNonAsciiText
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L78
     * @throws \Exception
     */
    public function textCreateWithNonAsciiText()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Здравствуй, мир!',
            'uri' => 'path'
        ]));
        $this->assertResponseOk();
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('mode', 1);
        $this->assertJsonResponseValueEquals('text', '<p>Здравствуй, мир!</p>');
    }

    /**
     * Port of isso python testCreateMultiple
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L89
     * @throws \Exception
     */
    public function testCreateMultiple()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 1);

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 2);

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 3);
    }

    /**
     * Port of isso python testCreateAndGetMultiple
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L99
     * @throws \Exception
     */
    public function testCreateAndGetMultiple()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
                'text' => 'Spam',
                'uri' => 'path'
            ]));
        }

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => 'path'
        ]));

        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('replies', 20);
    }

    /**
     * Port of isso python testCreateInvalidParent
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L110
     * @throws \Exception
     */
    public function testCreateInvalidParent()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'parent' => 1,
            'uri' => 'test'
        ]));

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'parent' => 2,
            'uri' => 'test'
        ]));

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => 'path'
        ]));

        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('parent', 1);
    }

    /**
     * Port of isso python testVerifyFields
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L120
     * @throws \Exception
     */
    public function testVerifyFields()
    {

        $factory = function($q): Comment {
            $c = new Comment();
            $c->verify($q);
            return $c;
        };

        // text is missing
        $v = $factory([]);
        $this->assertFalse($v->isPassed());
        $this->assertArrayHasKey('text is missing', array_flip($v->getErrors()));

        // invalid types
        $v = $factory(["text" => "...", "parent" => "xxx"]);
        $this->assertFalse($v->isPassed());
        $this->assertArrayHasKey('parent must be an integer or null', array_flip($v->getErrors()));

        foreach (["author", "website", "email"] as $k) {
            $v = $factory(["text" => "...", $k => 3.14]);
            $this->assertFalse($v->isPassed());
            $this->assertArrayHasKey($k.' must be a string or null', array_flip($v->getErrors()));
        }
        unset($k);

        // text too short and/or blank

        foreach (['', "\n\n\n"] as $k) {
            $v = $factory(['text' => $k]);
            $this->assertFalse($v->isPassed());
            $this->assertArrayHasKey('text is too short (minimum length: 3)', array_flip($v->getErrors()));
        } unset($k);

        // email/website length
        $v = $factory(['text' => '...', 'parent' => null, 'author' => null, 'website' => null, 'email' => str_pad('*' , 254, '*')]);
        $this->assertTrue($v->isPassed());

        $v = $factory(['text' => '...', 'email' => str_pad('*' , 1024, '*')]);
        $this->assertFalse($v->isPassed());
        $this->assertArrayHasKey('http://tools.ietf.org/html/rfc5321#section-4.5.3', array_flip($v->getErrors()));

        $v = $factory(['text' => '...', 'parent' => null, 'author' => null, 'email' => null, 'website' => str_pad('google.de/a' , 128, 'a')]);
        $this->assertTrue($v->isPassed());

        $v = $factory(['text' => '...', 'parent' => null, 'author' => null, 'email' => null, 'website' => str_pad('google.de/a' , 1024, 'a')]);
        $this->assertFalse($v->isPassed());
        $this->assertArrayHasKey('website is too long (minimum length: 254)', array_flip($v->getErrors()));

        // valid website url

        $urls = [
            'valid' => [
                'example.tld',
                'http://example.tld',
                'https://example.tld',
                'https://example.tld:1337/',
                'https://example.tld:1337/foobar',
                'https://example.tld:1337/foobar?p=1#isso-thread'
            ],
            'invalid' => [
                'ftp://example.tld/',
                'tel:+1234567890',
                '+1234567890',
                'spam'
            ]
        ];

        foreach ($urls['valid'] as $u){
            $v = $factory(['text' => '...', 'parent' => null, 'author' => null, 'email' => null, 'website' => $u]);
            $this->assertTrue($v->isPassed());
        }

        foreach ($urls['invalid'] as $u){
            $v = $factory(['text' => '...', 'parent' => null, 'author' => null, 'email' => null, 'website' => $u]);
            $this->assertFalse($v->isPassed());
        }

    }

    // ---


    public function testCountRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/count', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"count"}', (string)$response->getBody());
    }

    public function testCountsRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/count', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"counts"}', (string)$response->getBody());
    }

    public function testFeedRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/feed', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"feed"}', (string)$response->getBody());
    }

    public function testViewRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"view","id":9999}', (string)$response->getBody());
    }

    public function testEditRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999', 'PUT'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"edit","id":9999}', (string)$response->getBody());
    }

    public function testDeleteRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999', 'DELETE'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"delete","id":9999}', (string)$response->getBody());
    }

    public function testGetModerateRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/activate/abc123', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"getModerate","id":9999,"action":"activate","key":"abc123"}', (string)$response->getBody());
    }

    public function testPostModerateRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/activate/abc123', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"postModerate","id":9999,"action":"activate","key":"abc123"}', (string)$response->getBody());
    }

    public function testLikeRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/like', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"like","id":9999}', (string)$response->getBody());
    }

    public function testDislikeRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/id/9999/dislike', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"dislike","id":9999}', (string)$response->getBody());
    }

    public function testDemoRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/demo', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"demo"}', (string)$response->getBody());
    }

    public function testPreviewRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/preview', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"preview"}', (string)$response->getBody());
    }

    public function testLoginRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/login', 'POST'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"login"}', (string)$response->getBody());
    }

    public function testAdminRoute()
    {
        $response = $this->runRequest(new ServerRequest([], [], '/admin', 'GET'));
        $this->assertResponseOk();
        $this->assertEquals('{"msg":"admin"}', (string)$response->getBody());
    }
}