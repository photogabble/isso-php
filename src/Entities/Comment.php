<?php

namespace App\Entities;
use App\Http\JsonFormat;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @see \App\Repositories\Comments
 * @Entity
 * @Table(name="comments")
 * @Entity(repositoryClass="App\Repositories\Comments")
 */
class Comment
{
    /**
     * Comment flagged as valid.
     */
    const MODE_VALID = 1;

    /**
     * Comment flagged as pending moderation.
     */
    const MODE_PENDING = 2;

    /**
     * Comment flagged as soft delete - it has been
     * deleted but has child comments attached.
     */
    const MODE_SOFT_DELETED = 4;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $tid;

    /**
     * @var int
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * Many Comments have One Thread
     * @var Thread
     * @ManyToOne(targetEntity="Thread", inversedBy="comments")
     * @JoinColumn(name="tid", referencedColumnName="id")
     */
    private $thread;

    /**
     * @var Comment
     * @ManyToOne(targetEntity="Comment", inversedBy="replies")
     * @JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var ArrayCollection|Comment[]
     * @OneToMany(targetEntity="Comment", mappedBy="parent")
     */
    private $replies;

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
     * Status of the comment:
     * 1: valid
     * 2: pending
     * 4: soft-deleted (unable to hard delete due to replies)
     *
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
     * @Column(type="string", nullable = true)
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
    private $likes = 0;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $dislikes = 0;

    /**
     * @var string
     * @Column(type="blob")
     */
    private $voters = '';

    public function __construct() {
        $this->replies = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param Thread $thread
     */
    public function setThread(Thread $thread): void
    {
        $this->thread = $thread;
    }

    /**
     * @param Comment $parent
     */
    public function setParent(Comment $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @param float $created
     */
    public function setCreated(float $created): void
    {
        $this->created = $created;
    }

    /**
     * @param float $modified
     */
    public function setModified(float $modified): void
    {
        $this->modified = $modified;
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @param string $remote_addr
     */
    public function setRemoteAddr(string $remote_addr): void
    {
        $this->remote_addr = $remote_addr;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    /**
     * @param int $likes
     */
    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    /**
     * @param int $dislikes
     */
    public function setDislikes(int $dislikes): void
    {
        $this->dislikes = $dislikes;
    }

    /**
     * @param int $voters
     */
    public function setVoters(int $voters): void
    {
        $this->voters = $voters;
    }

    public function toJsonFormat(): JsonFormat
    {
        $format = new JsonFormat();
        $format->id = $this->id;
        $format->parent = is_null($this->parent) ? null : $this->parent->getId();
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