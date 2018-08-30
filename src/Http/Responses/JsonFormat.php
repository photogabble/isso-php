<?php

namespace App\Http\Responses;

//
// FIELDS = set(['id', 'parent', 'text', 'author', 'website',
//                  'mode', 'created', 'modified', 'likes', 'dislikes', 'hash', 'gravatar_image', 'notification'])
//

use App\Entities\Comment;

class JsonFormat
{
    /**
     * comment id (unique per website).
     * @var int
     */
    public $id = 0;

    /**
     * parent id reference, may be null.
     * @var null|int
     */
    public $parent = null;

    /**
     * required, comment written in Markdown.
     * @var string
     */
    public $text = '';

    /**
     * author’s name, may be null.
     * @var null|string
     */
    public $author = null;

    /**
     * author’s website, may be null.
     * @var null|string
     */
    public $website = null;

    /**
     * 1 – accepted
     * 2 – in moderation queue
     * 4 – deleted, but referenced.
     * @var int
     */
    public $mode = 2;

    /**
     * time in seconds since UNIX time.
     * @var float
     */
    public $created = 0.0;

    /**
     * last modification since UNIX time, may be null.
     * @var null|float
     */
    public $modified = null;

    /**
     * upvote count, defaults to 0.
     * @var int
     */
    public $likes = 0;

    /**
     * downvote count, defaults to 0.
     * @var int
     */
    public $dislikes = 0;

    /**
     * user identication, used to generate identicons.
     * PBKDF2 from email or IP address (fallback).
     * @var string
     */
    public $hash = '';

    public $gravatar_image;

    public $notification = false;

    /**
     * @var int
     */
    public $hidden_replies = 0;

    /**
     * @var array
     */
    public $replies = [];

    /**
     * @var int
     */
    public $total_replies = 0;

    public function __construct(Comment $comment)
    {
        $this->id = $comment->getId();
        $this->parent = is_null($comment->getParent()) ? null : $comment->getParent()->getId();
        $this->text = $comment->getText();
        $this->author = $comment->getAuthor();
        $this->website = $comment->getWebsite();
        $this->created = $comment->getCreated();
        $this->modified = $comment->getModified();
        $this->likes = $comment->getLikes();
        $this->dislikes = $comment->getDislikes();
        $this->mode = $comment->getMode();
    }
}