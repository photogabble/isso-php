<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;

class Threads extends EntityRepository
{
    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L28
     * @param int $id
     */
    public function get(int $id)
    {
        // @todo
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.new
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L31
     * @param string $uri
     * @param string $title
     */
    public function new(string $uri, string $title)
    {
        // @todo
    }
}