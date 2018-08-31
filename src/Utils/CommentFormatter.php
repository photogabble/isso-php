<?php

namespace App\Utils;

use Adbar\Dot;
use App\Entities\Comment;
use App\Http\Responses\JsonFormat;
use Doctrine\ORM\EntityManagerInterface;
use Parsedown;
use Zend\Diactoros\Response\JsonResponse;

class CommentFormatter
{
    /**
     * @var Dot
     */
    private $configuration;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * JsonFormatFactory constructor.
     * @param EntityManagerInterface $entityManager
     * @param Dot $configuration
     * @param Hasher $hasher
     */
    public function __construct(EntityManagerInterface $entityManager, Dot $configuration, Hasher $hasher)
    {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->hasher = $hasher;
    }

    /**
     * @todo add caching
     * @param Comment $comment
     * @param bool $plain should the text md be parsed?
     * @return JsonFormat
     * @throws \Exception
     */
    public function createJsonFormatFromComment(Comment $comment, bool $plain): JsonFormat
    {
        $parseDown = new Parsedown();
        $parseDown->setSafeMode(true);

        $format = new JsonFormat($comment);
        $format->hash = $this->hasher->hash($comment->getEmail() || $comment->getRemoteAddr());
        $format->text = ($plain === false) ? $parseDown->text($format->text) : $format->text;

        if ($this->configuration->get('gravatar', false) === true) {
            $format->gravatar_image = str_replace('{}', md5($comment->getEmail()), $this->configuration->get('gravatar-url'));
        }
        return $format;
    }

    /**
     * @param array|Comment[] $comments
     * @param bool $plain
     * @return array|JsonResponse[]
     * @throws \Exception
     */
    public function processFetchedList(array $comments, bool $plain): array {
        foreach ($comments as &$comment){
            $comment = $this->createJsonFormatFromComment($comment, $plain);
        } unset($comment);
        return $comments;
    }
}