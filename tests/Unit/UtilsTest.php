<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;

class UtilsTest extends BootsApp
{
    /**
     * Port of isso python test_anonymize
     * @see https://github.com/posativ/isso/blob/master/isso/tests/test_utils.py#L11
     * @throws \Exception
     */
    public function testAnonymize()
    {
        foreach([
            '12.34.56.78' => '12.34.56.0',
            '1234:5678:90ab:cdef:fedc:ba09:8765:4321' => '1234:5678:90ab:0000:0000:0000:0000:0000',
            '::ffff:127.0.0.1' => '127.0.0.0'
        ] as $input => $expected) {
            // @todo assert utility does the job
        }
        $this->markTestIncomplete('Not yet implemented.');
    }
}