<?php

namespace App\Entities;

/**
 * @Entity
 * @Table(name="comments")
 */
class Comment
{
    /**
     * @var int
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $tid;

    /**
     * @var int
     * @Column(type="integer", nullable = true)
     */
    private $parent;

    /**
     * @var float
     * @Column(type="float")
     */
    private $created;

    /**
     * @var float
     * @Column(type="float", nullable = true)
     */
    private $modified;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $mode;

    /**
     * @var string
     * @Column(type="string")
     */
    private $remote_addr;

    /**
     * @var string
     * @Column(type="text")
     */
    private $text;

    /**
     * @var string
     * @Column(type="string")
     */
    private $author;

    /**
     * @var string
     * @Column(type="string", nullable = true)
     */
    private $email;

    /**
     * @var string
     * @Column(type="string", nullable = true)
     */
    private $website;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $likes;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $dislikes;

    /**
     * @var int
     * @Column(type="blob")
     */
    private $voters;

}