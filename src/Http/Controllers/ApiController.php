<?php
namespace App\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ApiController extends Controller
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return \Zend\Diactoros\Response\JsonResponse
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        return new JsonResponse(['msg' => 'hello world']);
    }
}