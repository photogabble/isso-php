<?php

namespace App\Services;

use Adbar\Dot;
use App\Http\Responses\JsonResponseFactory;
use App\Utils\CommentFormatter;
use App\Utils\Hasher;
use Doctrine\ORM\EntityManagerInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class Responses extends AbstractServiceProvider
{
    /** @var array */
    protected $provides = [
        JsonResponseFactory::class,
        CommentFormatter::class
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

            /** @var EntityManagerInterface $em */
            $em = $this->getContainer()->get(EntityManagerInterface::class);

            return new JsonResponseFactory($em, new Dot($config->get('general', [])), $this->getContainer()->get(Hasher::class));
        });

        $this->getContainer()->add(CommentFormatter::class, function(){
            /** @var Dot $config */
            $config = $this->getContainer()->get('config');

            /** @var EntityManagerInterface $em */
            $em = $this->getContainer()->get(EntityManagerInterface::class);

            return new CommentFormatter($em, new Dot($config->get('general', [])), $this->getContainer()->get(Hasher::class));
        });
    }
}