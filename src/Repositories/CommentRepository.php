<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{

    /**
     * Add new comment to DB and return a mapping of :attribute:`fields` and
     * database values.
     *
     * Port of isso python isso.db.comments.add
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L43
     * @param string $uri
     * @param $c
     */
    public function add(string $uri, array $c)
    {
        // @todo
    }

    /**
     * Activate comment id if pending.
     *
     * Port of isso python isso.db.comments.activate
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L75
     * @param int $id
     */
    public function activate(int $id)
    {
        // @todo
    }

    /**
     * Turn off email notifications for replies to this comment.
     *
     * Port of isso python isso.db.comments.unsubscribe
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L84
     * @param string $mail
     * @param int $id
     */
    public function unsubscribe(string $mail, int $id)
    {
        // @todo
    }

    /**
     * Update comment :param:`id` with values from :param:`data` and return
     * updated comment.
     *
     * Port of isso python isso.db.comments.update
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L93
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data)
    {
        // @todo
    }

    /**
     * Search for comment :param:`id` and return a mapping of :attr:`fields`
     * and values.
     *
     * Port of isso python isso.db.comments.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L106
     * @param int $id
     */
    public function get(int $id)
    {
        // @todo
    }

    /**
     * Return comment mode counts for admin.
     *
     * Port of isso python isso.db.comments.count_modes
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L118
     */
    public function countModes()
    {
        // @todo
    }

    /**
     * Return comments for admin with :param:`mode`.
     *
     * Port of isso python isso.db.comments.fetchall
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L127
     * @param int $mode
     * @param int $after
     * @param int|null $parent
     * @param string $orderBy
     * @param int $limit
     * @param int $page
     * @param int $asc
     */
    public function fetchAll(int $mode = 5, int $after = 0, int $parent = null, string $orderBy = 'id', int $limit = 100, int $page = 0, int $asc = 1)
    {
        // @todo
    }

    /**
     * Return comments for :param:`uri` with :param:`mode`.
     *
     * Port of isso python isso.db.comments.fetch
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L176
     * @param string $uri
     * @param int $mode
     * @param int $after
     * @param int|null $parent
     * @param string $orderBy
     * @param int $asc
     * @param int|null $limit
     */
    public function fetch(string $uri, int $mode = 5, int $after = 0, int $parent = null, string $orderBy = 'id', int $asc = 1, int $limit = null)
    {
        // @todo
    }

    /**
     * Undocumented
     *
     * Port of isso python isso.db.comments._remove_stale
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L210
     */
    public function removeStale()
    {
        // @todo
    }

    /**
     * Delete a comment. There are two distinctions: a comment is referenced
     * by another valid comment's parent attribute or stand-a-lone. In this
     * case the comment can't be removed without losing depending comments.
     * Hence, delete removes all visible data such as text, author, email,
     * website sets the mode field to 4.
     *
     * In the second case this comment can be safely removed without any side
     * effects.
     *
     * Port of isso python isso.db.comments.delete
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L225
     * @param int $id
     */
    public function delete(int $id)
    {
        // @todo
    }

    /**
     * +1 a given comment. Returns the new like count (may not change because
     * the creater can't vote on his/her own comment and multiple votes from the
     * same ip address are ignored as well).
     *
     * Port of isso python isso.db.comments.vote
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L253
     * @param bool $upVote
     * @param int $id
     * @param string $remoteAddr
     */
    public function vote(bool $upVote = false, int $id, string $remoteAddr)
    {
        // @todo
    }

    /**
     * Return comment count for main thread and all reply threads for one url.
     *
     * Port of isso python isso.db.comments.reply_count
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L284
     * @param string $url
     * @param int $mode
     * @param int $after
     */
    public function replyCount(string $url, int $mode = 5, int $after = 0)
    {
        // @todo
    }

    /**
     * Return comment count for one ore more urls.
     *
     * Port of isso python isso.db.comments.count
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L298
     * @param array $urls
     * @return int|void
     */
    public function count(array $urls = [])
    {
        // @todo
    }

    /**
     * Undocumented
     *
     * Port of isso python isso.db.comments.purge
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L311
     * @param $delta
     */
    public function purge($delta)
    {
        // @todo
    }


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
        } else {
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
        } else {
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
