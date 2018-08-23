<?php

namespace App\Entities;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @see \App\Repositories\Threads
 * @Entity
 * @Table(name="threads")
 * @Entity(repositoryClass="App\Repositories\Threads")
 */
class Thread
{
    /**
     * @var int
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * One Thread has many Comments
     * @OneToMany(targetEntity="Comment", mappedBy="thread")
     */
    private $comments;

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

    /**
     * Thread constructor.
     */
    public function __construct() {
        $this->comments = new ArrayCollection();
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}