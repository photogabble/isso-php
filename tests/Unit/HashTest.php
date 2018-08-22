<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;
use App\Utils\Hasher;

class HashTest extends BootsApp
{
    /**
     * Port of isso python test_hash
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils_hash.py#L15
     * @throws \Exception
     */
    public function testHash()
    {
        $hasher = new Hasher('world');
        $this->assertEquals(md5('helloworld'), $hasher->hash('hello'));

        $hasher = new Hasher('world', 'sha1');
        $this->assertEquals(sha1('helloworld'), $hasher->hash('hello'));

        $this->expectExceptionMessage('Hash algorithm [abc] is not supported by your version of PHP.');
        new Hasher('world','abc');
    }

    /**
     * Port of isso python test_uhash
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils_hash.py#L28
     * @throws \Exception
     */
    public function testUHash()
    {
        $this->markTestSkipped('Not yet implemented; not sure if it needs porting.'); // @todo not sure if this needs porting...
    }

    /**
     * Port of isso python test_default
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils_hash.py#L35
     * @throws \Exception
     */
    public function testPBKDF2Default()
    {
        $hasher = new Hasher('world', 'pbkdf2');
        $this->assertEquals('3ad933085ca0', $hasher->hash(''));
    }

    /**
     * Port of isso python test_different_salt
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils_hash.py#L40
     * @throws \Exception
     */
    public function testPBKDF2DifferentSalt()
    {
        $a = new Hasher('hello', 'pbkdf2');
        $b = new Hasher('world', 'pbkdf2');

        $this->assertNotEquals($a->hash('...'), $b->hash('...'));
    }

    /**
     * Port of isso python test_custom
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils_hash.py#L48
     * @throws \Exception
     */
    public function testCustom()
    {
        $hasher = new Hasher('hello', 'pbkdf2:1000:2:md5');
        $this->assertEquals(2, strlen($hasher->hash('...')));

        $hasher = new Hasher('hello', 'pbkdf2:1000:64:sha1');
        $this->assertEquals(64, strlen($hasher->hash('...')));
    }
}
