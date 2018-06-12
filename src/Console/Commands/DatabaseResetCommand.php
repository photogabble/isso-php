<?php

namespace App\Console\Commands;

use App\Console\ConsoleCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Component\Console\Input\InputOption;

class DatabaseResetCommand extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('db:reset')
            ->setDescription('Reset the database to default, this will kill all data!')
            ->addOption('force', 'F', InputOption::VALUE_NONE, 'Force the command to work, this will wipe all data!');
    }

    protected function fire()
    {
        if (! $this->input->getOption('force')) {
            $this->output->writeln('[!] This command will not run without the -force flag.');
            $this->output->writeln('    Running this command will wipe all data on the target database!');
            return 1;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);

        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        try {
            $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
        } catch (ToolsException $e) {
            $this->output->writeln('[!] There was an error resetting the database.');
            $this->output->writeln('[!] ' . $e->getMessage());
            return 1;
        }

        $this->output->writeln('Database reset, all migrations run.');
        return 0;
    }
}