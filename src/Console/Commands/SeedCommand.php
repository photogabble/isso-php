<?php

namespace App\Console\Commands;

use App\Console\ConsoleCommand;
use App\Entities\Comment;
use App\Entities\Thread;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class SeedCommand extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('db:seed')
            ->setDescription('Seeds database with data.');
    }

    private function emptyTable(EntityManagerInterface $em, $className)
    {
        $cmd = $em->getClassMetadata($className);
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            //$connection->query('SET FOREIGN_KEY_CHECKS=0');
            $connection->query('DELETE FROM ' . $cmd->getTableName());
            // Beware of ALTER TABLE here--it's another DDL statement and will cause
            // an implicit commit.
            //$connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $connection->rollback();
            return $e->getMessage();
        }
    }

    /**
     * @return int
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function fire()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);

        $entities = [
            Thread::class,
            Comment::class,
        ];

        $progress = new ProgressBar($this->output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %message% %memory:6s%');
        $progress->setRedrawFrequency(250);

        foreach ($entities as $entity) {
            $clear = $this->emptyTable($em, $entity);
            if ($clear === true) {
                $this->output->writeln('Entity [' . $entity . '] Cleared.');
            } else {
                $this->output->writeln('There was an error clearing [' . $entity . '].');
                $this->output->writeln($clear);
                return 1;
            }
        }

        $progress->start((10*250));

        for ($i = 0; $i < 10; $i++) {
            $thread = new Thread();
            $thread->setUri('/site-page-' . $i);
            $thread->setTitle('Page Number [' . $i . ']');
            $progress->setMessage('Page Number [' . $i . ']');
            $em->persist($thread);

            for ($n = 0; $n < 250; $n++) {
                $comment = new Comment();
                $comment->setCreated(time());
                $comment->setMode(1);
                $comment->setText('Hello world!');
                $comment->setThread($thread);
                $comment->setRemoteAddr('127.0.0.1');
                $em->persist($comment);
                $progress->advance();

                if ($n <=5) {
                    for ($x = 0; $x < 3; $x++) {
                        $child = new Comment();
                        $child->setCreated(time());
                        $child->setMode(1);
                        $child->setParent($comment);
                        $child->setText('Child Comment ['.$x.']');
                        $child->setThread($thread);
                        $child->setRemoteAddr('127.0.0.1');
                        $em->persist($child);
                    }
                }
            }
            $em->flush();
        }

        $em->flush();
        return 0;
    }
}