<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;

class ParseTest extends BootsApp
{
    /**
     * Port of isso python test_thread
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils.py#L25
     * @throws \Exception
     */
    public function testThread()
    {
        $this->assertEquals('Untitled', parseTitleFromHTML(''));
        $this->assertEquals('Untitled', parseTitleFromHTML('<section id="isso-thread" data-isso-id="Fuu.">'));
        $this->assertEquals('Untitled', parseTitleFromHTML('<html><head></head><body>Hello World!</body></html>'));

        $this->assertEquals('Foo!', parseTitleFromHTML('<html><head><title>PHP-ISSO Unit Test</title></head><body id="isso-thread" data-title="Foo!">Hello World!</body></html>'));
        $this->assertEquals('PHP-ISSO Unit Test', parseTitleFromHTML('<html><head><title>PHP-ISSO Unit Test</title></head><body id="isso-thread">Hello World!</body></html>'));
        $this->assertEquals('First Title', parseTitleFromHTML('<html><head><title>First Title</title><title>This is ignored</title></head><body>Hello World!</body></html>'));
        $this->assertEquals('No way!', parseTitleFromHTML('<html><body><h1>I\'m the real title!1<section data-title="No way!" id="isso-thread"></body>'));
        $this->assertEquals('Can you find me?', parseTitleFromHTML('<html><head><title>PHP-ISSO Unit Test</title></head><body><header><h1>generic website title</h1></header><article><header><h1>Can you find me?</h1></header><section id="isso-thread"></section></section></article></body></html>'));
    }

    public function testParseStringToTime()
    {
        $this->assertEquals(13512, parseStringToTime('3h45m12s'));
        $this->assertEquals(13512, parseStringToTime('3h 45m 12s'));
        $this->assertEquals(13512, parseStringToTime('12s3h45m'));
    }
}