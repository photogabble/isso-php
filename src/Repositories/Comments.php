<?php

namespace App\Repositories;

use App\Entities\Comment;
use App\Entities\Thread;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class Comments
 *
 * Port of isso python isso.db.comments and isso.db.guard as they
 * both do actions on the comment entity.
 * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py
 * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py
 */
class Comments extends EntityRepository
{

    /**
     * Add new comment to DB and return a mapping of :attribute:`fields` and
     * database values.
     *
     * Port of isso python isso.db.comments.add
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L43
     * @param Thread $thread
     * @param array $c
     * @return Comment
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(Thread $thread, array $c)
    {
        $entity = new Comment();
        $entity->setThread($thread);

        if (!is_null($c['parent']) && $parent = $this->get($c['parent'])) {

            // @todo #39 make max nesting level configurable?
            if ($this->getNestingLevel($parent) > 1) {
                $parent = $parent->getParent();
            }

            $entity->setParent($parent);
        }

        $entity->setCreated(isset($c['created']) ? $c['created'] : time());
        $entity->setMode($c['mode']);
        $entity->setRemoteAddr($c['remote_addr']);
        $entity->setText($c['text']);
        $entity->setAuthor($c['author']);
        $entity->setEmail($c['email']);
        $entity->setWebsite($c['website']);
        $entity->setNotification($c['notification']);
        $entity->setVoters();

        $this->getEntityManager()->persist($entity);

        return $entity;
    }

    /**
     * Activate comment id if pending.
     *
     * Port of isso python isso.db.comments.activate
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L75
     * @see https://github.com/photogabble/isso-php/issues/24
     * @param int $id
     */
    public function activate(int $id)
    {
        // @todo #24
    }

    /**
     * Turn off email notifications for replies to this comment.
     *
     * Port of isso python isso.db.comments.unsubscribe
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L84
     * @see https://github.com/photogabble/isso-php/issues/25
     * @param string $mail
     * @param int $id
     */
    public function unsubscribe(string $mail, int $id)
    {
        // @todo #25
    }

    /**
     * Update comment :param:`id` with values from :param:`data` and return
     * updated comment.
     *
     * Port of isso python isso.db.comments.update
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L93
     * @see https://github.com/photogabble/isso-php/issues/26
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data)
    {
        // @todo #26
    }

    /**
     * Search for comment :param:`id` and return a mapping of :attr:`fields`
     * and values.
     *
     * Port of isso python isso.db.comments.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L106
     * @param int $id
     * @return Comment|object|null
     */
    public function get(int $id): ?Comment
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Return comment mode counts for admin.
     *
     * Port of isso python isso.db.comments.count_modes
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L118
     * @see https://github.com/photogabble/isso-php/issues/27
     */
    public function countModes()
    {
        // @todo #27
    }

    /**
     * Return the nesting level of the input Comment.
     * This is used to limit new comment nesting
     * level to that which is configured.
     *
     * @param Comment $comment
     * @return int
     */
    public function getNestingLevel(Comment $comment): int
    {
        if (!$parent = $comment->getParent()) {
            return 0;
        }

        $level = 1;
        while (!is_null($parent)) {
            $parent = $parent->getParent();
            $level++;
        }

        return $level;
    }

    /**
     * Return comments for admin with :param:`mode`.
     *
     * Port of isso python isso.db.comments.fetchall
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L127
     * @see https://github.com/photogabble/isso-php/issues/28
     * @param string $uri
     * @param int $mode
     * @param int $after
     * @param int|null $parent
     * @param string $orderBy
     * @param int $limit
     * @param int $page
     * @param bool $asc
     * @return Comment[]
     */
    public function fetchAll(string $uri, int $mode = 5, int $after = 0, int $parent = null, string $orderBy = 'id', int $limit = 100, int $page = 0, bool $asc = true)
    {
        // @todo #28
    }

    /**
     * Return comments for :param:`uri` with :param:`mode`.
     *
     * Port of isso python isso.db.comments.fetch
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L176
     * @see https://github.com/photogabble/isso-php/issues/31
     * @param string $uri
     * @param int $mode
     * @param int $after
     * @param int|null $parent
     * @param string $orderBy
     * @param int $asc
     * @param int|null $limit
     * @return Comment[]
     */
    public function fetch(string $uri, int $mode = 5, int $after = 0, int $parent = null, string $orderBy = 'id', int $asc = 1, int $limit = null)
    {
        $q = $this->createQueryBuilder('c')
            ->addSelect('t')
            ->innerJoin('c.thread', 't', Join::WITH, 't.uri = :uri')
            ->andWhere('c.tid = t.id')// not sure needed...
            ->andWhere('BIT_OR(:mode, c.mode) = :mode')
            ->andWhere('c.created > :after')
            ->setParameters([
                'uri' => $uri,
                'mode' => $mode,
                'after' => $after
            ]);

        if (!is_null($parent)) {
            $q = $q->andWhere('c.parent = :parent');
            $q = $q->setParameter('parent', $parent);
        } else {
            $q = $q->andWhere('c.parent IS NULL');
        }

        if (!in_array($orderBy, ['id', 'created', 'modified', 'likes', 'dislikes'])) {
            $orderBy = 'id';
        }

        $q = $q->orderBy('c.' . $orderBy, $asc === true ? 'ASC' : 'DESC');

        if (!is_null($limit)) {
            $q = $q->setMaxResults($limit);
        }

        return $q->getQuery()
            ->getResult();
    }

    /**
     * Undocumented
     *
     * Port of isso python isso.db.comments._remove_stale
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L210
     * @see https://github.com/photogabble/isso-php/issues/29
     */
    public function removeStale()
    {
        // @todo #29
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
     * @see https://github.com/photogabble/isso-php/issues/30
     * @param int $id
     */
    public function delete(int $id)
    {
        // @todo #30
    }

    /**
     * +1 a given comment. Returns the new like count (may not change because
     * the creater can't vote on his/her own comment and multiple votes from the
     * same ip address are ignored as well).
     *
     * Port of isso python isso.db.comments.vote
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L253
     * @see https://github.com/photogabble/isso-php/issues/32
     * @param bool $upVote
     * @param int $id
     * @param string $remoteAddr
     */
    public function vote(bool $upVote = false, int $id, string $remoteAddr)
    {
        // @todo #32
    }

    /**
     * Return comment count for main thread and all reply threads for one url.
     *
     * Port of isso python isso.db.comments.reply_count
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L284
     * @param string $url
     * @param int $mode
     * @param int $after
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function replyCount(string $url, int $mode = 5, int $after = 0): array
    {
        $return = [];
        $query = $this->getEntityManager()
            ->getConnection()
            ->executeQuery(
                'SELECT comments.parent,count(*) as `c` FROM comments INNER JOIN threads ON threads.uri=? AND comments.tid=threads.id AND (? | comments.mode = ?) AND comments.created > ? GROUP BY comments.parent',
                [$url, $mode, $mode, $after],
                ['string', 'integer', 'integer', 'integer']
            );

        if (!$result = $query->fetchAll()) {
            return $return;
        }

        foreach ($result as $row) {
            $return[(int)$row->parent] = (int)$row->c;
        }

        return $return;
    }

    /**
     * Return comment count for one ore more urls.
     *
     * Port of isso python isso.db.comments.count
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L298
     * @see https://github.com/photogabble/isso-php/issues/33
     * @param array $urls
     * @return int|void
     */
    public function count(array $urls = [])
    {
        // @todo #33
    }

    /**
     * Undocumented
     *
     * Port of isso python isso.db.comments.purge
     * @see https://github.com/posativ/isso/blob/master/isso/db/comments.py#L311
     * @see https://github.com/photogabble/isso-php/issues/34
     * @param $delta
     */
    public function purge($delta)
    {
        // @todo #34
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.guard.validate
     * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py#L14
     * @see https://github.com/photogabble/isso-php/issues/35
     * @param string $uri
     * @param string $comment
     */
    public function guardValidate(string $uri, string $comment)
    {
        // @todo #35
    }

    /**
     * Returns count of comments authored by remote address
     * in the past 60 seconds.
     *
     * @param array $comment
     * @throws \Doctrine\DBAL\DBALException
     * @return int
     */
    public function countAuthoredByRemoteAddressInPastMinute(array $comment): int
    {
        $query = $this->getEntityManager()
            ->getConnection()
            ->prepare('SELECT COUNT(id) FROM comments WHERE remote_addr = :remote_addr AND :time - created < 60');

        $query->execute([
            'remote_addr' => $comment['remote_addr'],
            'time' => time()
        ]);

        return (int)$query->fetch()['COUNT(id)'];
    }

    /**
     * Returns count of direct comments authored by remote address
     * on a post.
     *
     * @param array $comment
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countAuthoredDirectResponseByRemoteAddress(array $comment): int
    {
        $query = $this->getEntityManager()
            ->getConnection()
            ->prepare('SELECT COUNT(id) FROM comments WHERE tid = (SELECT id FROM threads WHERE uri = :uri) AND remote_addr = :remote_addr AND parent IS NULL');

        $query->execute([
            'uri' => $comment['uri'],
            'remote_addr' => $comment['remote_addr']
        ]);

        return (int)$query->fetch()['COUNT(id)'];
    }

    /**
     * Used by Guard to block users from replying to their own
     * comments if reply-to-self is disabled and the parent
     * comment is still open for editing.
     *
     * @param array $comment
     * @param int $maxAge
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isAuthorsParentStillOpenForEditing(array $comment, int $maxAge): bool
    {
        $query = $this->getEntityManager()
            ->getConnection()
            ->prepare('SELECT id FROM comments WHERE remote_addr = :remote_addr AND id = :id AND :time - created < :max_age');

        $query->execute([
            'remote_addr' => $comment['remote_addr'],
            'id' => $comment['parent'],
            'time' => time(),
            'max_age' => $maxAge

        ]);

        return (int)$query->fetch()['COUNT(id)'] === 0;
    }

    /**
     * Undocumented.
     *
     * Port of isso python isso.db.guard._spam
     * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py#L75
     * @param string $uri
     * @param string $comment
     * @return bool
     */
    public function guardSpam(string $uri, string $comment)
    {
        return true;
    }

    //
    // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // //
    //


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
