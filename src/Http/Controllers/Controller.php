<?php

namespace App\Http\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use Photogabble\Tuppence\App;

class Controller
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var App
     */
    protected $app;

    /**
     * ContentType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param App $app
     */
    public function __construct(EntityManagerInterface $entityManager, App $app)
    {
        $this->entityManager = $entityManager;
        $this->app = $app;
    }
}