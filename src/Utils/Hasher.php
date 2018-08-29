<?php

namespace App\Utils;
/**
 * Class Hasher
 *
 * Port of isso python isso.utils.hash
 * @see https://github.com/posativ/isso/blob/master/isso/utils/hash.py
 */
class Hasher
{
    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * Hasher constructor.
     *
     * Create a new Hasher from the hash configuration. If an algorithm
     * takes custom parameters, you can separate them by colon like
     * this: pbkdf2:arg1:arg2:arg3.
     *
     * @param string $salt
     * @param string $algorithm
     * @throws \Exception
     */
    public function __construct(string $salt, string $algorithm = 'md5')
    {
        if (strpos($algorithm, 'pbkdf2') === false) {
            if (! in_array($algorithm, hash_algos())){
                throw new \Exception(sprintf('Hash algorithm [%s] is not supported by your version of PHP.', $algorithm));
            }
        }

        if (empty($salt)){
            throw new \Exception('A salt for hashing is required.');
        }

        $this->salt = $salt;
        $this->algorithm = $algorithm;
    }

    /**
     * Calculate hash from value.
     * Defaults to md5.
     *
     * @param mixed $value
     * @return string
     * @throws \Exception
     */
    public function hash($value): string
    {
        // defaults: pbkdf2:1000:12:sha1
        if (strpos($this->algorithm, 'pbkdf2') !== false){
            $parts = explode(':', $this->algorithm);

            $config = [
                isset($parts[1]) ? (int) $parts[1] : 1000,
                isset($parts[2]) ? (int) $parts[2] : 12,
                isset($parts[3]) ? $parts[3] : 'sha1'
            ];

            list($iterations, $dkLen, $algorithm) = $config;

            if (! in_array($algorithm, hash_algos())){
                throw new \Exception(sprintf('Hash algorithm [%s] is not supported by your version of PHP.', $algorithm));
            }

            return hash_pbkdf2($algorithm, $value, $this->salt, $iterations, $dkLen);
        }

        return hash($this->algorithm, $value.$this->salt);
    }
}