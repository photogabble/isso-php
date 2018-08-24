<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class Guard extends AbstractServiceProvider
{
    /** @var array */
    protected $provides = [
        \App\Utils\Guard::class
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
        $this->getContainer()->add(\App\Utils\Guard::class, function(){
            return new \App\Utils\Guard(
                $this->getContainer()->get(EntityManagerInterface::class),
                $this->getContainer()->get('config')
            );
        });
    }
}