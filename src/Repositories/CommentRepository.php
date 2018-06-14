<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;

class CommentRepository extends  EntityRepository
{
    public function lookupCommentsByUri(string $uri, int $mode, $offset = null, $limit = 100)
    {
        $q = $this->createQueryBuilder('c')
            ->addSelect('t')
            ->innerJoin('c.threads', 't')
            ->where('t.uri = :uri')
            ->andWhere('c.mode = :mode')
            ->setParameters([
                'uri' => $uri,
                'mode' => $mode
            ])
            ->getQuery();

        if (! is_null($offset)) {
            $q->setFirstResult($offset);
        }

        if (! is_null($limit))
        {
            $q->setMaxResults($limit);
        }

        return $q->getResult();
    }
}
