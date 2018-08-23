<?php

namespace App\Repositories;

use App\Entities\Thread;
use Doctrine\ORM\EntityRepository;

class Threads extends EntityRepository
{
    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.__contains__
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L21
     * @param string $uri
     * @return bool
     */
    public function contains(string $uri): bool
    {
        return false; // @todo
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.__getitem__
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L25
     * @param string $uri
     * @return Thread
     */
    public function getThreadByUri(string $uri): ?Thread
    {
        // @todo
        return null;
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L28
     * @param int $id
     * @return Thread
     */
    public function get(int $id): Thread
    {
        // @todo
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.new
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L31
     * @throws \Doctrine\ORM\ORMException
     * @param string $uri
     * @param string $title
     * @return Thread
     */
    public function new(string $uri, string $title): Thread
    {
        $thread = new Thread();
        $thread->setUri($uri);
        $thread->setTitle($title);

        $this->getEntityManager()->persist($thread);
        return $thread;
    }
}