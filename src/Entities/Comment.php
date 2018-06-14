<?php

namespace App\Entities;
use App\Http\JsonFormat;

/**
 * @Entity
 * @Table(name="comments")
 * @Entity(repositoryClass="App\Repositories\CommentRepository")
 */
class Comment
{
    /**
     * @var int
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * Many Comments have One Thread
     * @ManyToOne(targetEntity="Thread", inversedBy="comments")
     * @JoinColumn(name="tid", referencedColumnName="id")
     */
    private $thread;

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

    public function toJsonFormat(): JsonFormat
    {
        $format = new JsonFormat();
        $format->id = $this->id;
        $format->parent = $this->parent;
        $format->text = $this->text;
        $format->author = $this->author;
        $format->website = $this->website;
        $format->created = $this->created;
        $format->modified = $this->modified;
        $format->likes = $this->likes;
        $format->dislikes = $this->dislikes;
        $format->hash = hash_pbkdf2('sha256', $this->text.$this->author.$this->created.$this->modified, 'Eech7co8Ohloopo9Ol6baimi', 100, 12);

        return $format;
    }
}