<?php

namespace App\Utils;
use RocketLabs\BloomFilter\Persist\BitString;

/**
 * Class BloomFilter
 *
 * A space-efficient probabilistic data structure.
 *
 * This class wraps \RocketLabs\BloomFilter\BloomFilter in order to provide
 * the functionality that is found in isso.
 *
 * @see http://code.activestate.com/recipes/577684-bloom-filter/
 * @see https://github.com/posativ/isso/blob/master/isso/utils/__init__.py#L44
 * @see https://github.com/makinacorpus/php-bloom/blob/master/BloomFilter.php
 * @see https://github.com/dsx724/php-bloom-filter/blob/master/bloomfilter.php
 */
class BloomFilter
{
    /**
     * Max Items.
     *
     * @var int
     */
    private $max = 256;

    /**
     * @var \RocketLabs\BloomFilter\BloomFilter
     */
    private $filter;

    /**
     * BloomFilter constructor.
     *
     * @param array $iterable
     * @param float $probability the allowed false positive rate.
     */
    public function __construct(array $iterable = [], $probability = 0.0001)
    {
        $m = (int)ceil(($this->max * (log($probability)) / (log(2) ** 2)) * -1);
        $k = (int)ceil($m / $this->max * log(2));

        $this->filter = new \RocketLabs\BloomFilter\BloomFilter(new BitString(), $m, $k, ['Murmur']);
        if (count($iterable) > 0) {
            $this->filter->addBulk($iterable);
        }
    }

    /**
     * Add a value to the filter.
     *
     * @param string $value
     */
    public function add(string $value)
    {
        $this->filter->add($value);
    }

    /**
     * Check if the filter contains a value.
     *
     * @param string $value
     * @return bool
     */
    public function contains(string $value): bool
    {
        return $this->filter->has($value);
    }
}