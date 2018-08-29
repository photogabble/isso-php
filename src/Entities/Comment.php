<?php

namespace App\Entities;

use App\Utils\BloomFilter;
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
     * @todo add magic getter that uses this property to expose private methods
     * @var array
     */
    private $allowed = ['id', 'parent', 'text', 'author', 'website', 'mode', 'created', 'modified', 'likes', 'dislikes', 'hash', 'gravatar_image', 'notifications'];

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
     * @var BloomFilter
     * @Column(type="blob")
     */
    private $voters = '';

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    private $notification;

    public function __construct()
    {
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
     * @return Thread
     */
    public function getThread(): Thread
    {
        return $this->thread;
    }

    /**
     * @param Comment $parent
     */
    public function setParent(Comment $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Comment
     */
    public function getParent(): Comment
    {
        return $this->parent;
    }

    /**
     * @param float $created
     */
    public function setCreated(float $created): void
    {
        $this->created = $created;
    }

    /**
     * @return float
     */
    public function getCreated(): float
    {
        return $this->created;
    }

    /**
     * @param float $modified
     */
    public function setModified(float $modified): void
    {
        $this->modified = $modified;
    }

    /**
     * @return float
     */
    public function getModified(): float
    {
        return $this->modified;
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param string $remote_addr
     */
    public function setRemoteAddr(string $remote_addr): void
    {
        $this->remote_addr = $remote_addr;
    }

    /**
     * @return string
     */
    public function getRemoteAddr():string
    {
        return $this->remote_addr;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText():string
    {
        return $this->text;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param int $likes
     */
    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    /**
     * @return int
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param int $dislikes
     */
    public function setDislikes(int $dislikes): void
    {
        $this->dislikes = $dislikes;
    }

    /**
     * @return int
     */
    public function getDislikes():int
    {
        return $this->dislikes;
    }

    /**
     * @param BloomFilter $voters
     */
    public function setVoters(BloomFilter $voters = null): void
    {
        if (is_null($voters)) {
            $voters = new BloomFilter();
        }
        $this->voters = serialize($voters);
    }

    /**
     * @return BloomFilter
     */
    public function getVoters(): BloomFilter
    {
        $value = unserialize($this->voters);
        if (!$value instanceof BloomFilter) {
            $value = new BloomFilter();
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function getNotification(): bool
    {
        return $this->notification;
    }

    /**
     * @param bool $notification
     */
    public function setNotification(bool $notification): void
    {
        $this->notification = $notification;
    }

    /**
     * @return Comment[]|ArrayCollection
     */
    public function getReplies()
    {
        return $this->replies;
    }
}