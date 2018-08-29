<?php

namespace App\Http\Responses;

use Adbar\Dot;
use App\Entities\Comment;
use App\Entities\Preference;
use App\Repositories\Preferences;
use App\Utils\Hasher;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Doctrine\ORM\EntityManagerInterface;
use Parsedown;
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
     * @param Comment $comment
     * @return JsonResponse|ResponseInterface
     * @throws \Exception
     */
    public function createFromNewComment(Comment $comment): ResponseInterface
    {
        $parseDown = new Parsedown();
        $parseDown->setSafeMode(true);

        $format = new JsonFormat($comment);
        $format->hash = $this->hasher->hash($comment->getEmail() || $comment->getRemoteAddr());
        $format->text = $parseDown->text($format->text);

        if ($this->configuration->get('gravatar', false) === true) {
            $format->gravatar_image = str_replace('{}', md5($comment->getEmail()), $this->configuration->get('gravatar-url'));
        }

        return FigResponseCookies::set(
            (new JsonResponse($format, ($comment->getMode() === Comment::MODE_PENDING) ? 202 : 201)),
            SetCookie::create(sprintf('isso-%d', $comment->getId()))
                ->withValue($this->getUrlSafeSignedCookieValue((string)$comment->getId()))
                ->withMaxAge(parseStringToTime($this->configuration->get('max-age')))
        );
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