<?php

namespace App\Tests\Feature;

use App\Tests\BootsApp;
use App\Utils\BloomFilter;

class BloomTest extends BootsApp
{
    public function testBloomFilter()
    {
        $max = 256;

        try {
            $has = [];
            $hasNot = [];

            for ($i = 0; $i < $max; $i++) {
                array_push($has, sprintf('%d.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(0, 255), random_int(0, 255)));
            } unset($i);

            while(count($hasNot) < $max) {
                $v = sprintf('%d.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(0, 255), random_int(0, 255));
                if (in_array($v, $has)) { continue; }
                array_push($hasNot, $v);
            } unset($v);

        } catch (\Exception $e) {
            $this->fail($e);
        }


        $bloom = new BloomFilter($has);

        foreach ($has as $item) {
            $this->assertTrue($bloom->contains($item));
        } unset($item);

        $falsePositives = 0;
        foreach ($hasNot as $item) {
            if ( $bloom->contains($item) === true) {
                $falsePositives++;
            }
        } unset($item);

        $this->assertTrue($falsePositives <= 1);
    }
}