<?php

namespace App\Services;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
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

        $app->get('/', '\App\Http\Controllers\ApiController::index');
    }
}