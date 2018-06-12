<?php

namespace App\Exceptions;

use Exception;
use Photogabble\Tuppence\App;
use Photogabble\Tuppence\ErrorHandlers\DefaultExceptionHandler;
use League\Route\Http\Exception\NotFoundException as RouteNotFoundException;
use League\Container\Exception\NotFoundException as ContainerNotFoundException;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Handler extends DefaultExceptionHandler
{
    /**
     * Exceptions this handler should ignore and pass through.
     *
     * @var array
     */
    protected $ignore = [];

    /**
     * @var App
     */
    private $app;

    /**
     * Handler constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param Exception|RouteNotFoundException|ContainerNotFoundException $e
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function __invoke(Exception $e, RequestInterface $request)
    {
        if (in_array(get_class($e), $this->ignore)) {
            throw $e;
        }
        $status = (method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        return new JsonResponse([
            'message' => $e->getMessage(),
            'trace' => explode(PHP_EOL, $e->getTraceAsString())
        ], $status);
    }
}