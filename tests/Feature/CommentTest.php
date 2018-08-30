<?php

namespace App\Tests\Feature;

use App\Entities\Thread;
use App\Http\Controllers\ApiController;
use App\Http\Validation\Comment;
use App\Repositories\Comments;
use App\Repositories\Threads;
use App\Tests\BootsApp;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Diactoros\ServerRequest;

class CommentTest extends BootsApp
{
    /**
     * Port of isso python testGet
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L53
     * @throws \Exception
     */
    public function testGet()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->app->getContainer()->get(EntityManagerInterface::class);

        /** @var Comments $comments */
        $comments = $em->getRepository(\App\Entities\Comment::class);

        /** @var Threads $threads */
        $threads = $em->getRepository(Thread::class);

        $thread = $threads->new('Test', '/path/');

        $comments->add($thread, [
            'parent' => null,
            'remote_addr' => '127.0.0.1',
            'text' => 'Lorem ipsum ...',
            'author' => '',
            'email' => '',
            'website' => '',
            'notification' => false,
            'mode' => 1
        ]);
        $em->flush();

        $this->makeRequest('GET', '/id/1');
        $this->assertResponseOk();
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
        $this->app->getContainer()->get('config')->set('moderation.enabled', false);

        $this->makeRequest('POST', '/new?uri=%2Fpath%2F', [
            'text' => 'Lorem ipsum ...'
        ]);

        $this->assertResponseStatusCodeEquals(201);
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
        $this->app->getContainer()->get('config')->set('moderation.enabled', false);

        $this->makeRequest('POST', '/new?uri=%2Fpath%2F', [
            'text' => 'Здравствуй, мир!'
        ]);

        $this->assertResponseStatusCodeEquals(201);
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

        $this->assertResponseStatusCodeEquals(201);
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 1);

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));

        $this->assertResponseStatusCodeEquals(201);
        $this->assertJsonResponse();
        $this->assertJsonResponseValueEquals('id', 2);

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));

        $this->assertResponseStatusCodeEquals(201);
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

            $this->assertResponseStatusCodeEquals(201);
        }

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => 'path'
        ]));

        $this->assertResponseOk();
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

        $this->assertResponseOk();
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

    /**
     * Port of isso python testGetInvalid
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L159
     * @throws \Exception
     */
    public function testGetInvalid()
    {
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/',
            'id' => 123
        ]));

        $this->assertResponseStatusCodeEquals(404);
    }

    /**
     * Port of isso python testGetLimited
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L166
     * @throws \Exception
     */
    public function testGetLimited()
    {
        for ($i=0; $i<20;$i++) {
            $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
                'text' => '...',
                'uri' => 'test'
            ]));

            $this->assertResponseStatusCodeEquals(201);
        }

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => 'test',
            'limit' => 10
        ]));

        $this->assertResponseOk();

        $replies = $this->getJsonResponseValue('replies');
        $this->assertTrue(is_array($replies));
        $this->assertCount(10, $replies);
    }

    /**
     * Port of isso python testGetNested
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L177
     * @throws \Exception
     */
    public function testGetNested()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));

        $this->assertResponseStatusCodeEquals(201);

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test',
            'parent' => 1
        ]));

        $this->assertResponseStatusCodeEquals(201);

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => 'test',
            'parent' => 1
        ]));

        $this->assertResponseOk();

        $replies = $this->getJsonResponseValue('replies');
        $this->assertTrue(is_array($replies));
        $this->assertCount(1, $replies);
    }

    /**
     * Port of isso python testGetLimitedNested
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L189
     * @throws \Exception
     */
    public function testGetLimitedNested()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));

        $this->assertResponseStatusCodeEquals(201);

        for ($i=0; $i<20;$i++) {
            $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
                'text' => '...',
                'uri' => 'test',
                'parent' => 1
            ]));

            $this->assertResponseStatusCodeEquals(201);
        }

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => 'test',
            'parent' => 1,
            'limit' => 10
        ]));

        $this->assertResponseOk();

        $replies = $this->getJsonResponseValue('replies');
        $this->assertTrue(is_array($replies));
        $this->assertCount(10, $replies);
    }

    /**
     * Port of isso python testUpdate
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L202
     * @throws \Exception
     */
    public function testUpdate()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Lorem ipsum ...',
            'uri' => '/path/'
        ]));

        $this->assertResponseStatusCodeEquals(201);

        $this->runRequest(new ServerRequest([], [], '/new', 'PUT', 'php://input', [], [], [
            'text' => 'Hello World',
            'author' => 'me',
            'website' => 'http://example.com/',
            'uri' => '/path/'
        ]));

        $this->assertResponseOk();

        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/',
            'plain' => 1
        ]));

        $this->assertResponseOk();

        $this->assertJsonResponseValueEquals('text','Hello World');
        $this->assertJsonResponseValueEquals('author','me');
        $this->assertJsonResponseValueEquals('website','http://example.com/');
        $this->assertJsonResponseHasKey('modified');
    }

    /**
     * Port of isso python testDelete
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L218
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Lorem ipsum ...',
            'uri' => '/path/'
        ]));

        $this->assertResponseStatusCodeEquals(201);

        $this->runRequest(new ServerRequest([], [], '/id/1', 'DELETE'));

        $this->assertResponseOk();
        $this->assertJsonResponseEmpty();

        $this->runRequest(new ServerRequest([], [], '/id/1', 'GET'));

        $this->assertResponseStatusCodeEquals(404);
    }

    /**
     * Port of isso python testDeleteWithReference
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L227
     * @throws \Exception
     */
    public function testDeleteWithReference()
    {
        // Add parent comment
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'First',
            'uri' => '/path/'
        ]));

        $this->assertResponseStatusCodeEquals(201);

        // Add child comment
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Second',
            'parent' => 1,
            'uri' => '/path/'
        ]));

        $this->assertResponseStatusCodeEquals(201);

        // Delete parent comment
        $this->runRequest(new ServerRequest([], [], '/id/1', 'DELETE'));

        $this->assertResponseOk();
        $this->assertJsonResponseValueEquals('mode', 4);

        // @todo self.assertIn('/path/', self.app.db.threads)

        // Assert parent comment returns 200 by uri even if content is deleted
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/'
        ]));

        $this->assertResponseOk();
        $this->assertJsonResponseValueEquals('total_replies', 1);

        // Assert parent comment returns 200 by id even if content is deleted (maybe check the response doesn't return content?)
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/',
            'id' => 1
        ]));

        $this->assertResponseOk();

        // Assert child comment returns 200
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/',
            'id' => 2
        ]));

        $this->assertResponseOk();

        // Delete child comment
        $this->runRequest(new ServerRequest([], [], '/id/2', 'DELETE'));

        $this->assertResponseOk();

        // Assert no comments returned for uri
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/'
        ]));

        $this->assertResponseStatusCodeEquals(404);

        // @todo self.assertNotIn('/path/', self.app.db.threads)
    }

    /**
     * [ comment 1 ]
     * |
     * --- [ comment 2, ref 1 ]
     * |
     * --- [ comment 3, ref 1 ]
     * [ comment 4 ]
     *
     * Port of isso python testDeleteWithMultipleReferences
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L249
     * @throws \Exception
     */
    public function testDeleteWithMultipleReferences()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'First',
            'uri' => '/path/'
        ]));

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Second',
            'parent' => 1,
            'uri' => '/path/'
        ]));

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Third',
            'parent' => 1,
            'uri' => '/path/'
        ]));

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Last',
            'uri' => '/path/'
        ]));

        $this->runRequest(new ServerRequest([], [], '/id/1', 'DELETE'));
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/'
        ]));
        $this->assertResponseOk();

        $this->runRequest(new ServerRequest([], [], '/id/2', 'DELETE'));
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/'
        ]));
        $this->assertResponseOk();

        $this->runRequest(new ServerRequest([], [], '/id/3', 'DELETE'));
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/'
        ]));
        $this->assertResponseOk();

        $this->runRequest(new ServerRequest([], [], '/id/4', 'DELETE'));
        $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
            'uri' => '/path/'
        ]));
        $this->assertResponseStatusCodeEquals(404);
    }

    /**
     * Port of isso python testPathVariations
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L276
     * @throws \Exception
     */
    public function testPathVariations()
    {
        $paths = ['/sub/path/', '/path.html', '/sub/path.html', 'path', '/'];

        foreach ($paths as $path)
        {
            $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
                'text' => '...',
                'uri' => $path
            ]));

            $this->assertResponseStatusCodeEquals(201);
        }

        foreach ($paths as $i => $path)
        {
            $this->runRequest(new ServerRequest([], [], '/', 'GET', 'php://input', [], [], [
                'uri' => $path
            ]));
            $this->assertResponseOk();

            $this->runRequest(new ServerRequest([], [], '/'.($i+1), 'GET'));
            $this->assertResponseOk();
        }
    }

    /**
     * Port of isso python testDeleteAndCreateByDifferentUsersButSamePostId
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L289
     * @throws \Exception
     */
    public function testDeleteAndCreateByDifferentUsersButSamePostId()
    {
        $this->markTestIncomplete('Not yet implemented.');
    }

    /**
     * Port of isso python testHash
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L301
     * @throws \Exception
     */
    public function testHash()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Aaa...',
            'uri' => '/path/'
        ]));

        $a = $this->getDecodedJsonResponse();

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Bbb...',
            'uri' => '/path/'
        ]));

        $b = $this->getDecodedJsonResponse();

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => 'Ccc...',
            'email' => '...',
            'uri' => '/path/'
        ]));

        $c = $this->getDecodedJsonResponse();

        $this->assertNotEquals('192.168.1.1', $a['hash']);
        $this->assertEqual($a['hash'], $b['hash']);
        $this->assertNotEquals($a['hash'], $c['hash']);
    }

    /**
     * Port of isso python testVisibleFields
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L316
     * @throws \Exception
     */
    public function testVisibleFields()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'invalid' => 'field',
            'uri' => '/path/'
        ]));
        $this->assertResponseStatusCodeEquals(201);

        $data = $this->getDecodedJsonResponse();

        foreach (ApiController::$fields as $key) {
            unset($data[$key]);
        } unset ($key);

        $this->assertCount(0, $data);
    }

    /**
     * Port of isso python testNoFeed
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L330
     * @throws \Exception
     */
    public function testNoFeed()
    {
        $this->runRequest(new ServerRequest([], [], '/feed', 'POST', 'php://input', [], [], [
            'uri' => '/path/nothing/'
        ]));
        $this->assertResponseStatusCodeEquals(404);
    }

    /**
     * Port of isso python testFeedEmpty
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L334
     * @throws \Exception
     */
    public function testFeedEmpty()
    {
        $this->markTestIncomplete('Not yet implemented.');
    }

    /**
     * Port of isso python testFeed
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L344
     * @throws \Exception
     */
    public function testFeed()
    {
        $this->markTestIncomplete('Not yet implemented.');
    }

    /**
     * Port of isso python testCounts
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L360
     * @throws \Exception
     */
    public function testCounts()
    {
        $this->markTestIncomplete('Not yet implemented.');
    }

    /**
     * Port of isso python testMultipleCounts
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L382
     * @throws \Exception
     */
    public function testMultipleCounts()
    {
        $this->markTestIncomplete('Not yet implemented.');
    }

    /**
     * Port of isso python testModify
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L394
     * @throws \Exception
     */
    public function testModify()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'uri' => 'test',
            'text' => 'Ypyo'
        ]));

        $this->runRequest(new ServerRequest([], [], '/new', 'PUT', 'php://input', [], [], [
            'uri' => 'test',
            'text' => 'Tyop'
        ]));

        $this->runRequest(new ServerRequest([], [], '/id/1', 'GET'));
        $this->assertJsonResponseValueEquals('text', '<p>Tyop</p>');

        $this->runRequest(new ServerRequest([], [], '/new', 'PUT', 'php://input', [], [], [
            'uri' => 'test',
            'text' => 'Typo'
        ]));

        $this->runRequest(new ServerRequest([], [], '/id/1', 'GET'));
        $this->assertJsonResponseValueEquals('text', '<p>Typo</p>');
    }

    /**
     * Port of isso python testDeleteCommentRemovesThread
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L403
     * @throws \Exception
     */
    public function testDeleteCommentRemovesThread()
    {
        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'uri' => 'test',
            'text' => '...'
        ]));

        // @todo self.assertIn('/', self.app.db.threads)

        $this->runRequest(new ServerRequest([], [], '/id/1', 'DELETE'));

        // @todo self.assertNotIn('/', self.app.db.threads)
    }

    /**
     * Port of isso python testCSRF
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L410
     * @throws \Exception
     */
    public function testCSRF()
    {
        $this->markTestIncomplete('Not yet implemented.');
    }

    /**
     * Port of isso python testPreview
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L430
     * @throws \Exception
     */
    public function testPreview()
    {
        $this->runRequest(new ServerRequest([], [], '/preview', 'POST', 'php://input', [], [], [
            'text' => 'This is **mark***down*'
        ]));

        $this->assertResponseOk();

        $this->assertJsonResponseValueEquals('text', '<p>This is <strong>mark</strong><em>down</em></p>');
    }
}