<?php
namespace App\Http\Controllers;

use Adbar\Dot;
use App\Entities\Comment;
use App\Entities\Thread;
use App\Repositories\Comments;
use App\Repositories\Threads;
use Doctrine\ORM\EntityManagerInterface;
use Photogabble\Tuppence\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ApiController extends Controller
{

    /**
     * Comment fields, that can be submitted in request.
     * @var array
     */
    private $accept = ['text', 'author', 'website', 'email', 'parent', 'title', 'notification'];

    /**
     * Default fields that are sent in response. (Public fields.)
     * @var array
     */
    private $fields = ['id', 'parent', 'text', 'author', 'website', 'mode', 'created', 'modified', 'likes', 'dislikes', 'hash', 'gravatar_image', 'notification'];

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var bool
     */
    private $moderation;

    public function __construct(EntityManagerInterface $entityManager, App $app)
    {
        parent::__construct($entityManager, $app);

        /** @var Dot $config */
        $config = $app->getContainer()->get('config');
        $this->moderation = $config->get('moderation.enabled', true);
        $this->configuration = new Dot($config->get('general', []));
    }

    /**
     * POST: /new
     *
     * Port of isso python isso.views.comments.fetch
     * @see https://github.com/posativ/isso/blob/master/isso/views/comments.py#L247
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postNew(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        $q = new Dot(array_filter($request->getQueryParams(), function($k) {
            return in_array($k, $this->accept);
        }, ARRAY_FILTER_USE_KEY));

        foreach (["author", "email", "website", "parent"] as $k) {
            if (!$q->has($k)){
                $q->set($k, '');
            }
        }

        $v = new \App\Http\Validation\Comment();
        if (! $v->verify($q->flatten())) {
            return new JsonResponse($v->getErrors(), 400);
        }

        foreach (["author", "email", "website"] as $field) {
            $q->set($field, htmlspecialchars($q->get($field, '')));
        }

        if (strlen($q->get('website')) > 0) {
            $q->set('website', $this->normaliseUrl($q->get('website')));
        }

        $q->set('mode', ( $this->moderation ? 2 : 1 ));

        // @todo upgrade Tuppence to PSR-15 and use https://github.com/middlewares/psr15-middlewares to get ip
        $q->set('remote_addr', '127.0.0.1');

        /** @var Threads $threads */
        $threads = $this->entityManager->getRepository(Thread::class);


        if ($threads->contains($q->get('uri'))) {
            $thread = $threads->getThreadByUri($q->get('uri'));
        } else {
            // If title not set then attempt to parse the title of the referring url
            if (! $q->has('title')) {
                // @todo https://github.com/posativ/isso/blob/master/isso/views/comments.py#L271-L285
            }

            try{
                $thread = $threads->new($q->get('uri'), $q->get('title'));
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Database error'],  500);
            }

        }

        // @todo finish me

        $this->entityManager->flush(); // Commit all changes to disk...

        return new JsonResponse([]);
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
            return 'http://'.$url;
        }
        return $url;
    }

    /**
     * GET: /
     *
     * Port of isso python isso.views.comments.fetch
     * @see https://github.com/posativ/isso/blob/master/isso/views/comments.py#L247
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFetch(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $q = $request->getQueryParams();

        $args = array_merge([
            'uri' => isset($q['uri']) ? (string) $q['uri'] : '',
            'after' => isset($q['after']) ? (int) $q['after'] : 0,
            'parent' => isset($q['parent']) ? (int) $q['parent'] : null,
            'limit' => isset($q['limit']) ? (int) $q['limit'] : 100,
            'nested_limit' => isset($q['nested_limit']) ? (int) $q['nested_limit'] : 0,
            'plain' => isset($q['plain']) ? ($q['plain'] === '1') : false,
        ], $args);

        /** @var Comments $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        $count = $repository->countCommentsByUri($args['uri'], 1, $args['parent']);
        $replies = $repository->lookupCommentsByUri($args['uri'], 1, $args['parent'], $args['after'], $args['limit']);

        return new JsonResponse([
            'hidden_replies' => $count > 0 ? max($count - $args['limit'], 0) : 0,
            'id' => $args['parent'],
            'replies' => array_map(function(Comment $comment){
                return $comment->toJsonFormat();
            }, $replies),
            'total_replies' => $count
        ]);
    }

    /**
     * GET: /count
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function getCount(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'count']);
    }

    /**
     * POST: /count
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postCount(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'counts']);
    }

    /**
     * GET: /feed
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function getFeed(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'feed']);
    }

    /**
     * GET: /id/<int:id>
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function getView(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'view', 'id' => (int) $args['id']]);
    }

    /**
     * PUT: /id/<int:id>
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function putEdit(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'edit', 'id' => (int) $args['id']]);
    }

    /**
     * DELETE: /id/<int:id>
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function deleteDelete(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'delete', 'id' => (int) $args['id']]);
    }

    /**
     * GET: /id/<int:id>/<any(edit,activate,delete):action>/<string:key>
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function getModerate(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'getModerate', 'id' => (int) $args['id'], 'action' => $args['action'], 'key' => $args['key']]);
    }

    /**
     * POST: /id/<int:id>/<any(edit,activate,delete):action>/<string:key>
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postModerate(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'postModerate', 'id' => (int) $args['id'], 'action' => $args['action'], 'key' => $args['key']]);
    }

    /**
     * POST: /id/<int:id>/like
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postLike(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'like', 'id' => (int) $args['id']]);
    }

    /**
     * POST: /id/<int:id>/dislike
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postDislike(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'dislike', 'id' => (int) $args['id']]);
    }

    /**
     * GET: /demo
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function getDemo(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'demo']);
    }

    /**
     * POST: /preview
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postPreview(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'preview']);
    }

    /**
     * POST: /login
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postLogin(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'login']);
    }

    /**
     * GET: /admin
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function getAdmin(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'admin']);
    }
}