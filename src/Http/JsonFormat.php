<?php

namespace App\Http;

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
     * 1 – accepted
     * 2 – in moderation queue
     * 4 – deleted, but referenced.
     * @var int
     */
    public $mode = 2;

    /**
     * user identication, used to generate identicons.
     * PBKDF2 from email or IP address (fallback).
     * @var string
     */
    public $hash = '';

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
}