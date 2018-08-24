<?php

namespace App\Utils;

use Adbar\Dot;
use App\Entities\Comment;
use App\Repositories\Comments;
use Doctrine\ORM\EntityManagerInterface;

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
     * @var null|string
     */
    private $error;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var int
     */
    private $maxTime;

    /**
     * Guard constructor.
     * @param EntityManagerInterface $entityManager
     * @param Dot $configuration
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManager, Dot $configuration)
    {
        $this->maxTime = parseStringToTime($configuration->get('general.max-age', '15m'));

        if (! $maxAge = strtotime($configuration->get('general.max-time', '15m')))
        $this->configuration = $configuration->get('guard');
        $this->entityManager = $entityManager;
    }

    /**
     * Undocumented.
     *
     * @see https://github.com/posativ/isso/blob/master/isso/db/spam.py#L14
     * @param string $uri
     * @param $comment
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function validate(string $uri, $comment): bool
    {
        $this->error = null;

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
     * @throws \Doctrine\DBAL\DBALException
     */
    private function checkLimit(string $uri, $comment): bool
    {
        /** @var Comments $comments */
        $comments = $this->entityManager->getRepository(Comment::class);

        // Block more than :param:`ratelimit` comments per minute

        $rateLimit = $this->configuration->get('guard.ratelimit', 3);

        if ($comments->countAuthoredByRemoteAddressInPastMinute($comment) >= $rateLimit){
            $this->error = sprintf('%s: ratelimit exceeded (%d)', $comment['remote_addr'], $rateLimit);
            return false;
        }

        // Block more than three comments as direct response to the post
        if (is_null($comment['parent'])) {
            $directReplyLimit = $this->configuration->get('guard.direct-reply', 3);

            if ($comments->countAuthoredDirectResponseByRemoteAddress($comment) >= $directReplyLimit){
                $this->error = sprintf('%n direct responses to %s', $directReplyLimit, $comment['uri']);
                return false;
            }

        // block replies to self unless :param:`reply-to-self` is enabled
        } elseif (! $this->configuration->get('guard.reply-to-self', false)) {
            if ($comments->isAuthorsParentStillOpenForEditing($comment, $this->maxTime) > 0) {
                $this->error = 'edit time frame is still open';
                return false;
            }
        }

        // require email if :param:`require-email` is enabled
        if ($this->configuration->get('guard.require-email', false ) === true && empty($comment['email'])) {
            $this->error = 'email address required but not provided';
            return false;
        }

        // require author if :param:`require-author` is enabled
        if ($this->configuration->get('guard.require-author', false ) === true && empty($comment['author'])) {
            $this->error = 'author address required but not provided';
            return false;
        }

        return true;
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