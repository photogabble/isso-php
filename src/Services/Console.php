<?php

namespace App\Services;

use App\Console\Commands\DatabaseResetCommand;
use App\Console\Commands\SeedCommand;
use App\Console\ConsoleCommand;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Symfony\Component\Console\Application;

class Console extends AbstractServiceProvider
{
    /** @var array */
    protected $provides = [
        Application::class
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
        $this->getContainer()->add(Application::class, function(){
            $application = new Application('Unwrappathon-2017', '1.0.0');

            $commands = [
                new SeedCommand(),
                new DatabaseResetCommand(),
            ];

            /** @var ConsoleCommand $command */
            foreach ($commands as $command)
            {
                $command->setContainer($this->getContainer());
                $application->add($command);
            }
            return $application;
        });
    }
}