<?php

namespace App\Services;

use Adbar\Dot;
use App\Utils\Hasher;
use League\Container\ServiceProvider\AbstractServiceProvider;

class Hashing extends AbstractServiceProvider
{
    /** @var array */
    protected $provides = [
        Hasher::class
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
        $this->getContainer()->add(Hasher::class, function(){

            /** @var Dot $config */
            $config = $this->getContainer()->get('config');

            return new Hasher($config->get('hash.salt', null), $config->get('hash.algorithm', null));
        });
    }
}