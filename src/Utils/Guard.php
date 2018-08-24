<?php

namespace App\Utils;

use Adbar\Dot;

/**
 * Class Guard
 *
 * Port of isso python isso.db.guard
 * @see https://github.com/posativ/isso/blob/3d0fdffcb70bcff3c7f7ae28285e918a06655998/isso/db/spam.py
 */
class Guard
{
    /**
     * @var Dot
     */
    private $configuration;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * Guard constructor.
     * @param Dot $configuration
     */
    public function __construct(Dot $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Undocumented.
     *
     * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py#L14
     * @param string $uri
     * @param $comment
     * @return bool
     */
    public function validate(string $uri, $comment): bool
    {
        if (!$this->configuration->get('enabled', false)) {
            return true;
        }

        if (! $this->checkLimit($uri, $comment)) {
            return false;
        }

        if (! $this->checkSpam($uri, $comment)) {
            return false;
        }

        return true;
    }

    /**
     * Undocumented.
     *
     * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py#L29
     * @param string $uri
     * @param $comment
     * @return bool
     */
    private function checkLimit(string $uri, $comment): bool
    {

    }

    /**
     * Undocumented
     *
     * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py#L75
     * @param string $uri
     * @param $comment
     * @return bool
     */
    private function checkSpam(string $uri, $comment): bool
    {
        return true;
    }

}