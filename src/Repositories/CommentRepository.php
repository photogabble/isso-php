<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
    /**
     * @param string $uri
     * @param int $mode
     * @param int $parent
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countCommentsByUri(string $uri, int $mode, int $parent = null): int
    {
        $q = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->innerJoin('c.thread', 't')
            ->where('t.uri = :uri')
            ->andWhere('c.mode = :mode')
            ->setParameters([
                'uri' => $uri,
                'mode' => $mode
            ]);

        if (!is_null($parent)) {
            $q = $q->andWhere('c.parent = :parent');
            $q = $q->setParameter('parent', $parent);
        }else {
            $q = $q->andWhere('c.parent IS NULL');
        }

        return $q->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $uri
     * @param int $mode
     * @param int $parent
     * @param null $offset
     * @param int $limit
     * @return mixed
     */
    public function lookupCommentsByUri(string $uri, int $mode, int $parent = null, $offset = null, $limit = 100)
    {
        $q = $this->createQueryBuilder('c')
            ->addSelect('t')
            ->innerJoin('c.thread', 't')
            ->where('t.uri = :uri')
            ->andWhere('c.mode = :mode')
            ->setParameters([
                'uri' => $uri,
                'mode' => $mode
            ]);

        if (!is_null($parent)) {
            $q = $q->andWhere('c.parent = :parent');
            $q = $q->setParameter('parent', $parent);
        }else{
            $q = $q->andWhere('c.parent IS NULL');
        }

        if (!is_null($offset)) {
            $q = $q->setFirstResult($offset);
        }

        if (!is_null($limit)) {
            $q = $q->setMaxResults($limit);
        }

        return $q->getQuery()
            ->getResult();
    }
}
