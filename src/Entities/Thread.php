<?php

namespace App\Entities;

/**
 * @Entity
 * @Table(name="threads")
 */
class Thread
{
    /**
     * @var int
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @Column(type="string")
     */
    private $uri;

    /**
     * @var string
     * @Column(type="string")
     */
    private $title;
}