<?php

namespace App\Services;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Middlewares\JsonPayload;
use Photogabble\Tuppence\App;

class Routes extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        // ...
    }

    /**
     * Method will be invoked on registration of a service provider implementing
     * this interface. Provides ability for eager loading of Service Providers.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return void
     */
    public function boot()
    {
        /** @var App $app */
        $app = $this->getContainer()->get(App::class);

        $app->middleware(new JsonPayload);

        $app->get('/', '\App\Http\Controllers\ApiController::getFetch');
        $app->post('/new', '\App\Http\Controllers\ApiController::postNew');
        $app->get('/info', '\App\Http\Controllers\InfoController::show');

        $app->get('/count', '\App\Http\Controllers\ApiController::getCount');
        $app->post('/count', '\App\Http\Controllers\ApiController::postCount');

        $app->get('/feed', '\App\Http\Controllers\ApiController::getFeed');
        $app->get('/id/{id:number}', '\App\Http\Controllers\ApiController::getView');
        $app->put('/id/{id:number}', '\App\Http\Controllers\ApiController::putEdit');
        $app->delete('/id/{id:number}', '\App\Http\Controllers\ApiController::deleteDelete');
        $app->get('/id/{id:number}/{action}/{key}', '\App\Http\Controllers\ApiController::getModerate');
        $app->post('/id/{id:number}/{action}/{key}', '\App\Http\Controllers\ApiController::postModerate');

        $app->post('/id/{id:number}/like', '\App\Http\Controllers\ApiController::postLike');
        $app->post('/id/{id:number}/dislike', '\App\Http\Controllers\ApiController::postDislike');

        $app->get('/demo', '\App\Http\Controllers\ApiController::getDemo');
        $app->post('/preview', '\App\Http\Controllers\ApiController::postPreview');

        $app->post('/login', '\App\Http\Controllers\ApiController::postLogin');
        $app->get('/admin', '\App\Http\Controllers\ApiController::getAdmin');
    }
}