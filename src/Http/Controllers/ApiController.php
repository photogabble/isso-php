<?php
namespace App\Http\Controllers;

use App\Entities\Comment;
use App\Repositories\CommentRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ApiController extends Controller
{
    /**
     * GET: /
     *
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

        /** @var CommentRepository $repository */
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
     * POST: /new
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return JsonResponse
     */
    public function postNew(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $q = $request->getQueryParams();

        $args = array_merge([
            'uri' => isset($q['uri']) ? (string) $q['uri'] : '',
            'text' => isset($q['text']) ? (string) $q['text'] : '',
        ], $args);

        $id = null; // @todo finish this method

        return new JsonResponse(['id' => $id]);
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