<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

class Preferences extends EntityRepository
{

    private $defaults = [];

    /**
     * Preferences constructor.
     *
     * @param $em
     * @param Mapping\ClassMetadata $class
     * @throws \Exception
     */
    public function __construct($em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->defaults = [
            'session-key' => bin2hex(random_bytes(24)),
        ];

        foreach($this->defaults as $key => $value)
        {
            if (! $this->get($key)) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Port of isso python isso.db.preferences.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/preferences.py#L25
     * @param string $key
     * @param mixed|null $default
     */
    public function get(string $key, $default=null)
    {
        // @todo
    }

    /**
     * Port of isso python isso.db.preferences.set
     * @see https://github.com/posativ/isso/blob/master/isso/db/preferences.py#L34
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        // @todo
    }

}