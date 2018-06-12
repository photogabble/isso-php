<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;
use Zend\Diactoros\ServerRequest;

class RoutesTest extends BootsApp
{
    public function testRoute()
    {
        $n = $this->runRequest(new ServerRequest([[], [], '/', 'GET']));
        $this->assertResponseOk();
        $n =1;
    }
}