<?php

namespace App\Services;

use Adbar\Dot;
use App\Http\Responses\JsonResponseFactory;
use App\Utils\Hasher;
use League\Container\ServiceProvider\AbstractServiceProvider;

class Responses extends AbstractServiceProvider
{
    /** @var array */
    protected $provides = [
        JsonResponseFactory::class
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
        $this->getContainer()->add(JsonResponseFactory::class, function(){
            /** @var Dot $config */
            $config = $this->getContainer()->get('config');
            return new JsonResponseFactory(new Dot($config->get('general', [])), $this->getContainer()->get(Hasher::class));
        });
    }
}