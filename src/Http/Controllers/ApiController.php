<?php
namespace App\Http\Controllers;

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
     */
    public function getFetch(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
    public function postCounts(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
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
        return new JsonResponse(['msg' => 'fetch']);
    }
}