<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class Guzzle extends AbstractServiceProvider
{
    /** @var array */
    protected $provides = [
        ClientInterface::class
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->add(ClientInterface::class, function(){
            return new Client();
        });
    }
}