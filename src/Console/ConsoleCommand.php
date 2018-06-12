<?php

namespace App\Console;

use League\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ConsoleCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return $this->fire();
    }

    /**
     * @return int
     */
    abstract protected function fire();
}