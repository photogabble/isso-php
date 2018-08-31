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
     * @see https://github.com/photogabble/isso-php/issues/21
     * @param string $uri
     * @return bool
     */
    public function contains(string $uri): bool
    {
        return false; // @todo #21
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.__getitem__
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L25
     * @see https://github.com/photogabble/isso-php/issues/22
     * @param string $uri
     * @return Thread
     */
    public function getThreadByUri(string $uri): ?Thread
    {
        // @todo #22
        return null;
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.threads.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/threads.py#L28
     * @see https://github.com/photogabble/isso-php/issues/23
     * @param int $id
     * @return Thread
     */
    public function get(int $id): Thread
    {
        // @todo #23
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