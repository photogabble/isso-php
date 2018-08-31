<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;
use Zend\Diactoros\ServerRequest;

class ModeratedCommentsTest extends BootsApp
{
    /**
     * Port of isso python testAddComment
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_comments.py#L460
     * @see https://github.com/photogabble/isso-php/issues/72
     * @throws \Exception
     */
    public function testAddComment()
    {
        // conf.set("moderation", "enabled", "true")
        // conf.set("guard", "enabled", "off")
        // conf.set("hash", "algorithm", "none")

        $this->runRequest(new ServerRequest([], [], '/new', 'POST', 'php://input', [], [], [
            'text' => '...',
            'uri' => 'test'
        ]));

        $this->assertResponseStatusCodeEquals(202);

        $this->runRequest(new ServerRequest([], [], '/id/1', 'GET'));

        $this->assertResponseOk();

        $this->runRequest(new ServerRequest([], [], '/new', 'GET', 'php://input', [], [], [
            'uri' => 'test'
        ]));

        $this->assertResponseStatusCodeEquals(404);

        // self.app.db.comments.activate(1)

        $this->runRequest(new ServerRequest([], [], '/new', 'GET', 'php://input', [], [], [
            'uri' => 'test'
        ]));

        $this->assertResponseOk();

        $this->markTestIncomplete('Not yet completely ported.');
    }
}