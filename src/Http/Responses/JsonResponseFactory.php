<?php

namespace App\Http\Responses;

use Adbar\Dot;
use App\Entities\Comment;
use App\Entities\Preference;
use App\Repositories\Preferences;
use App\Utils\CommentFormatter;
use App\Utils\Hasher;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

class JsonResponseFactory
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
     * @var CommentFormatter
     */
    private $commentFormatter;

    /**
     * JsonFormatFactory constructor.
     * @param EntityManagerInterface $entityManager
     * @param CommentFormatter $commentFormatter
     * @param Dot $configuration
     * @param Hasher $hasher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CommentFormatter $commentFormatter,
        Dot $configuration,
        Hasher $hasher)
    {
        $this->commentFormatter = $commentFormatter;
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->hasher = $hasher;
    }

    /**
     * @param Comment $comment
     * @return JsonResponse|ResponseInterface
     * @throws \Exception
     */
    public function createFromNewComment(Comment $comment): ResponseInterface
    {
        return FigResponseCookies::set(
            (new JsonResponse($this->commentFormatter->createJsonFormatFromComment($comment, false), ($comment->getMode() === Comment::MODE_PENDING) ? 202 : 201)),
            SetCookie::create(sprintf('isso-%d', $comment->getId()))
                ->withValue($this->getUrlSafeSignedCookieValue((string)$comment->getId()))
                ->withMaxAge(parseStringToTime($this->configuration->get('max-age')))
        );
    }

    /**
     * @param Comment $comment
     * @param bool $plain
     * @return ResponseInterface
     * @throws \Exception
     */
    public function createFromSingleComment(Comment $comment, $plain = false): ResponseInterface
    {
        return new JsonResponse($this->commentFormatter->createJsonFormatFromComment($comment, $plain), 200);
    }

    /**
     * @param array $comments
     * @param array $replyCounts
     * @param array $args
     * @return ResponseInterface
     * @throws \Exception
     */
    public function createFromCommentCollection(array $comments, array $replyCounts, array $args): ResponseInterface
    {
        if (! in_array($args['parent'], array_keys($replyCounts))){
            $replyCounts[$args['parent']] = 0;
        }

        $response = [
            'id' => $args['parent'],
            'total_replies' => $replyCounts[$args['parent']],
            'hidden_replies' => max($replyCounts[$args['parent']] - count($comments), 0),
            'replies' => $this->commentFormatter->processFetchedList($comments, $args['plain'])
        ];

        /** @var JsonFormat $comment */
        foreach ($response['replies'] as &$comment)
        {
            if (isset($replyCounts[$comment->id])) {
                $comment->total_replies = $replyCounts[$comment->id];
            } else {
                $comment->total_replies = 0;
                $comment->replies = [];
            }
        } unset($comment);


        // @todo this is not finished, it doesn't return nested replies

        return new JsonResponse($response, count($response['replies']) > 0 ? 200 : 404);
    }

    /**
     * @param array $comments
     * @param bool $plain
     * @return ResponseInterface
     */
    public function createFromComments(array $comments, $plain = false): ResponseInterface
    {
        $comments = array_map(function(Comment $comment) use ($plain) {
            return $this->commentFormatter->createJsonFormatFromComment($comment, $plain);
        }, $comments);

        return new JsonResponse($comments, 200);
    }

    /**
     * @param string $input
     * @return string
     * @throws \Exception
     */
    private function getUrlSafeSignedCookieValue(string $input):string
    {
        /** @var Preferences $preferences */
        $preferences = $this->entityManager->getRepository(Preference::class);

        if (! $key = $preferences->get('session-key')) {
            throw new \Exception('Session key is not set.');
        }

        $hash = $this->hasher->hash((random_int(0,5000).microtime().$key));
        $signature = $this->hasher->hash($key.$hash.$input);
        return base64_encode($signature.'-'.$hash.'-'.$input);
    }
}