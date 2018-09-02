<?php

namespace App\Services;

use Adbar\Dot;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class Database extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /** @var array */
    protected $provides = [
        EntityManagerInterface::class,
        Connection::class,
        DebugStack::class
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
        /** @var Dot $configuration */
        $configuration = $this->getContainer()->get('config');

        $this->getContainer()->share(Connection::class, function () use ($configuration) {
            return DriverManager::getConnection($configuration['database'], new \Doctrine\DBAL\Configuration());
        });

        $this->getContainer()->share(DebugStack::class, new DebugStack());

        $this->getContainer()->share(EntityManagerInterface::class, function () use ($configuration) {
            $em = EntityManager::create(
                $configuration['database'],
                Setup::createAnnotationMetadataConfiguration(
                    [
                        realpath(__DIR__ . '/../Entities')
                    ],
                    $configuration['debug']
                )
            );

            if ($configuration->get('debug', false) === true) {
                $em->getConnection()->getConfiguration()->setSQLLogger($this->getContainer()->get(DebugStack::class));
            }

            return $em;
        });
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
        // ...
    }
}