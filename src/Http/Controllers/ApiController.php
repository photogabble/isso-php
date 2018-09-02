<?php

namespace App\Http\Controllers;

use Adbar\Dot;
use App\Entities\Comment;
use App\Entities\Thread;
use App\Http\Responses\JsonResponseFactory;
use App\Repositories\Comments;
use App\Repositories\Threads;
use App\Utils\CommentFormatter;
use App\Utils\Guard;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Photogabble\Tuppence\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

class ApiController extends Controller
{

    /**
     * Comment fields, that can be submitted in request.
     * @var array
     */
    static $accept = ['text', 'author', 'website', 'email', 'parent', 'title', 'notification', 'uri'];

    /**
     * Default fields that are sent in response. (Public fields.)
     * @var array
     */
    static $fields = ['id', 'parent', 'text', 'author', 'website', 'mode', 'created', 'modified', 'likes', 'dislikes', 'hash', 'gravatar_image', 'notification'];

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var bool
     */
    private $moderation;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var Guard
     */
    private $guard;
    /**
     * @var JsonResponseFactory
     */
    private $jsonResponseFactory;

    /**
     * @var CommentFormatter
     */
    private $commentFormatter;

    /**
     * ApiController constructor.
     * @param CommentFormatter $commentFormatter
     * @param JsonResponseFactory $jsonResponseFactory
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $guzzle
     * @param Guard $guard
     * @param App $app
     */
    public function __construct(
        CommentFormatter $commentFormatter,
        JsonResponseFactory $jsonResponseFactory,
        EntityManagerInterface $entityManager,
        ClientInterface $guzzle,
        Guard $guard,
        App $app)
    {
        parent::__construct($entityManager, $app);

        /** @var Dot $config */
        $config = $app->getContainer()->get('config');
        $this->moderation = $config->get('moderation.enabled', true);
        $this->configuration = new Dot($config->get('general', []));
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->guzzle = $guzzle;
        $this->guard = $guard;
        $this->commentFormatter = $commentFormatter;
    }

    /**
     * POST: /new
     *
     * Port of isso python isso.views.comments.fetch
     * @see https://github.com/posativ/isso/blob/master/isso/views/comments.py#L247
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function postNew(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        if (! is_array($request->getParsedBody())){
            return new EmptyResponse(400);
        }

        $q = new Dot(array_filter(array_merge($request->getQueryParams(), $request->getParsedBody()), function ($k) {
            return in_array($k, static::$accept);
        }, ARRAY_FILTER_USE_KEY));

        if (!$q->has('uri')) {
            return new EmptyResponse(400);
        }

        if (!$q->has('notification')) {
            $q->set('notification', 0);
        }

        foreach (["author", "email", "website", "parent"] as $k) {
            if (!$q->has($k)) {
                $q->set($k, (($k === 'parent') ? null : ''));
            }
        }

        $v = new \App\Http\Validation\Comment();
        if (!$v->verify($q->flatten())) {
            return new JsonResponse($v->getErrors(), 400);
        }

        foreach (["author", "email", "website"] as $field) {
            $q->set($field, htmlspecialchars($q->get($field, '')));
        }

        if (strlen($q->get('website')) > 0) {
            $q->set('website', $this->normaliseUrl($q->get('website')));
        }

        $q->set('mode', ($this->moderation ? 2 : 1));

        // @todo upgrade Tuppence to PSR-15 and use https://github.com/middlewares/psr15-middlewares to get ip
        $q->set('remote_addr', '127.0.0.1');

        /** @var Threads $threads */
        $threads = $this->entityManager->getRepository(Thread::class);

        // If thread record doesn't already exist, create one from the title param, or Referer if title isn't set.
        if (!$thread = $threads->getThreadByUri($q->get('uri'))) {
            // If title not set then attempt to parse the title of the referring url
            if (!$q->has('title')) {
                $origin = origin($request->getHeaderLine('Referer'));
                try {
                    $response = $this->guzzle->request('GET', $origin);
                } catch (GuzzleException $e) {
                    return new EmptyResponse(404);
                }
                $q->set('title', parseTitleFromHTML($response->getBody()));
            }

            try {
                $thread = $threads->new($q->get('uri'), $q->get('title'));

                // @todo #14: emit comments.new:new-thread event

            } catch (\Exception $e) {
                return new TextResponse('Database error', 500);
            }
        }

        // @todo #14: emit comments.new:before-save event

        if (!$this->guard->validate($q->get('uri'), $q->all())) {
            // @todo #14: emit comments.new:guard event
            return new TextResponse($this->guard->getError(), 403);
        }

        /** @var Comments $comments */
        $comments = $this->entityManager->getRepository(Comment::class);

        $rv = $comments->add($thread, $q->all());
        $this->entityManager->flush();

        // @todo #14 emit comments.new:after-save

        return $this->jsonResponseFactory->createFromNewComment($rv);
    }

    /**
     * Port of isso python isso.views.comments.normalize
     * @see https://github.com/posativ/isso/blob/master/isso/views/comments.py#L61
     * @param string $url
     * @return string
     */
    private function normaliseUrl(string $url): string
    {
        if (strpos($url, 'http://') === false || strpos($url, 'https://') === false) {
            return 'http://' . $url;
        }
        return $url;
    }

    /**
     * GET: /
     *
     * Port of isso python isso.views.comments.fetch
     * @see https://github.com/posativ/isso/blob/master/isso/views/comments.py#L732
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getFetch(ServerRequestInterface $request, array $args = [])
    {
        $q = $request->getQueryParams();

        // @todo #19 add the merge and validate to a Request object that can be consumed by other methods
        // @todo #20 all numbers should be validated as being a positive int

        // Validate limit at being a number
        if (isset($q['limit']) && !is_numeric($q['limit'])) {
            return new TextResponse('limit should be integer', 400);
        }

        // Validate parent as being a number
        if (isset($q['parent']) && !is_numeric($q['parent'])) {
            return new TextResponse('parent should be integer', 400);
        }

        // Validate nested_limit as being a number
        if (isset($q['nested_limit']) && !is_numeric($q['nested_limit'])) {
            return new TextResponse('nested_limit should be integer', 400);
        }

        $args = array_merge([
            'uri' => isset($q['uri']) ? (string)$q['uri'] : '',
            'after' => isset($q['after']) ? (int)$q['after'] : 0,
            'parent' => isset($q['parent']) ? (int)$q['parent'] : null,
            'limit' => isset($q['limit']) ? (int)$q['limit'] : 100,
            'nested_limit' => isset($q['nested_limit']) ? (int)$q['nested_limit'] : 0,
            'plain' => isset($q['plain']) ? ($q['plain'] === '1') : false,
        ], $args);

        /** @var Comments $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        if ($args['limit'] === 0) {
            $rootList = [];
        } else {
            $rootList = $repository->fetch($args['uri'], 5, $args['after'], $args['parent'], 'id', 1, $args['limit']);
        }

        // From here >>>>

        $replyCounts = $repository->replyCount($args['uri'], 5, $args['after']);

        if (! in_array($args['parent'], array_keys($replyCounts))){
            $replyCounts[$args['parent']] = 0;
        }

        $response = [
            'id' => $args['parent'],
            'total_replies' => $replyCounts[$args['parent']],
            'hidden_replies' => max($replyCounts[$args['parent']] - count($rootList), 0),
            'replies' => $this->commentFormatter->processFetchedList($rootList, $args['plain'])
        ];

        // <<<< to here should be contained within the JsonResponseFactory?

        if (is_null($args['parent'])){
            //throw new \Exception('This method is not yet finished... see todo below');
        }

        // @todo this is not finished, it doesn't return nested replies

        return new JsonResponse($response, count($response['replies']) > 0 ? 200 : 404);
    }

    /**
     * GET: /count
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function getCount(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'count']);
    }

    /**
     * POST: /count
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function postCount(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'counts']);
    }

    /**
     * GET: /feed
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function getFeed(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'feed']);
    }

    /**
     * GET: /id/<int:id>
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     * @throws \Exception
     */
    public function getView(ServerRequestInterface $request, array $args = [])
    {
        /** @var Comments $comments */
        $comments = $this->entityManager->getRepository(Comment::class);

        if (! $found = $comments->get((int)$args['id'])) {
            return new TextResponse('Not Found', 404);
        }

        return $this->jsonResponseFactory->createFromComment($found);
    }

    /**
     * PUT: /id/<int:id>
     *  Port of isso python isso.views.comments.edit
     *
     * @see https://github.com/posativ/isso/blob/master/isso/views/comments.py#L403
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function putEdit(ServerRequestInterface $request, array $args = [])
    {
        $n = $request->getCookieParams();
        return new JsonResponse(['msg' => 'edit', 'id' => (int)$args['id']]);
    }

    /**
     * DELETE: /id/<int:id>
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function deleteDelete(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'delete', 'id' => (int)$args['id']]);
    }

    /**
     * GET: /id/<int:id>/<any(edit,activate,delete):action>/<string:key>
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function getModerate(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'getModerate', 'id' => (int)$args['id'], 'action' => $args['action'], 'key' => $args['key']]);
    }

    /**
     * POST: /id/<int:id>/<any(edit,activate,delete):action>/<string:key>
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function postModerate(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'postModerate', 'id' => (int)$args['id'], 'action' => $args['action'], 'key' => $args['key']]);
    }

    /**
     * POST: /id/<int:id>/like
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function postLike(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'like', 'id' => (int)$args['id']]);
    }

    /**
     * POST: /id/<int:id>/dislike
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function postDislike(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'dislike', 'id' => (int)$args['id']]);
    }

    /**
     * GET: /demo
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function getDemo(ServerRequestInterface $request, array $args = [])
    {
        return new HtmlResponse(file_get_contents(APP_ROOT . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'demo.html') );
    }

    /**
     * POST: /preview
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function postPreview(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'preview']);
    }

    /**
     * POST: /login
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function postLogin(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'login']);
    }

    /**
     * GET: /admin
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function getAdmin(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse(['msg' => 'admin']);
    }
}