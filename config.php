<?php return [
    'debug' => true,

    /*
     * Database set up, Python isso uses SQLite3 and so does php-isso.
     * It's highly recommended to change the storage path to a
     * non-temporary location.
     */
    'database' => [
        'driver' => 'pdo_sqlite',
        'path' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'comments.sqlite',
    ],

    'general' => [
        /*
         * Required to dispatch multiple websites, not used otherwise.
         */
        'name' => env('ISSO_GENERAL_NAME', null),

        /*
         * Your website(s). If Isso is unable to connect to at least one site, you'll
         * get a warning during startup and comments are most likely non-functional.
         *
         * You'll need at least one host/website to run Isso. This is due to security
         * reasons: Isso uses CORS_ to embed comments and to restrict comments only to
         * your website, you have to "whitelist" your website(s).
         *
         * I recommend the first value to be a non-SSL website that is used as fallback
         * if Firefox users (and only those) suppress their HTTP referer completely:
         *
         * host =
         *      http://example.tld/
         *      https://example.tld/
         */
        'host' => [],

        /*
         * Time range that allows users to edit/remove their own comments.
         * It supports years, weeks, days, hours, minutes, seconds.
         * 3h45m12s equals to 3 hours, 45 minutes and 12 seconds.
         */
        'max-age' => env('ISSO_GENERAL_MAX_AGE', '15m'),

        /*
         * Select notification backend for new comments. Currently, only SMTP is
         * available.
         * Select notification backend(s) for new comments, separated by comma.
         *
         * Available back ends:
         *
         * stdout
         *     Log to standard output. Default, if none selected.
         * smtp
         *     Send notifications via SMTP on new comments with activation (if
         *     moderated) and deletion links.
         */
        'notify' => env('ISSO_GENERAL_NOTIFY', 'stdout'),

        /*
         * Allow users to request E-mail notifications for replies to their post.
         * WARNING: It is highly recommended to also turn on moderation when enabling
         * this setting, as Isso can otherwise be easily exploited for sending spam.
         */
        'reply-notifications' => (bool) env('ISSO_GENERAL_REPLY_NOTIFICATIONS', false),

        /*
         * Log console messages to file instead of standard output.
         *
         */
        'log-file' => env('ISSO_GENERAL_LOGFILE', null),

        /*
         * Adds property "gravatar_image" to json response when true
         * will automatically build md5 hash by email and use "gravatar_url" to build
         * the url to the gravatar image
         */
        'gravatar' => (bool) env('ISSO_GENERAL_GRAVATAR', false),

        /*
         * Default url for gravatar. {} is where the hash will be placed
         */
        'gravatar-url' => env('ISSO_GENERAL_GRAVATAR_URL', 'https://www.gravatar.com/avatar/{}?d=identicon'),

        /*
         * Admin access password
         * @todo replace this with better auth...
         */
        'admin_password' => env('ISSO_GENERAL_ADMIN_PASSWORD', 'please_choose_a_strong_password')
    ],

    'moderation' => [
        /*
         * Enable comment moderation queue. This option only affects new comments.
         * Comments in moderation queue are not visible to other users until you activate
         * them.
         */
        'enabled' => (bool) env('ISSO_MODERATION_ENABLED', true),

        /*
         * Remove unprocessed comments in moderation queue after given time.
         */
        'purge-after' => env('ISSO_MODERATION_PURGE_AFTER', '30d'),
    ],

    'guard' => [

        /*
         * Enable basic spam protection features, e.g. rate-limit per IP address (/24
         * for IPv4, /48 for IPv6).
         * enable guard, recommended in production. Not useful for debugging purposes.
         */
        'enabled' => (bool) env('ISSO_GUARD_ENABLED', true),

        /*
         * Limit to N new comments per minute.
         */
        'ratelimit' => (int) env('ISSO_GUARD_RATELIMIT', 2),

        /*
         * How many comments directly to the thread (prevent a simple while true; do
         * curl ...; done.
         */
        'direct-reply' => (int) env('ISSO_GUARD_DIRECT_REPLY', 3),

        /*
         * Allow commenter's to reply to their own comments when they could still edit
         * the comment. After the editing time frame is gone, commenter's can reply to
         * their own comments anyways. Do not forget to configure the client.
         */
        'reply-to-self' => (bool) env('ISSO_GUARD_REPLY_TO_SELF', false),

        /*
         * Force commenter's to enter a value into the author field. No validation is
         * performed on the provided value.  Do not forget to configure the client
         * accordingly.
         */
        'require-author' => (bool) env('ISSO_GUARD_REQUIRE_AUTHOR', false),

        /*
         * Require the commenter to enter an email address (note: no validation is
         * done on the provided address). Do not forget to configure the client.
         */
        'require-email' => (bool) env('ISSO_GUARD_REQUIRE_EMAIL', false)
    ],

    'markup' => [
        // @todo
    ],

    /*
     * Customize used hash functions to hide the actual email addresses from
     * commenter's but still be able to generate an identicon.
     */
    'hash' => [
        /*
         * A salt is used to protect against rainbow tables. Isso does not make use of
         * pepper (yet). The default value has been in use since the release of Isso and
         * generates the same identicons for same addresses across installations.
         */
        'salt' => env('ISSO_HASH_SALT', 'Eech7co8Ohloopo9Ol6baimi'),

        /*
         * Hash algorithm to use -- either from Python's hashlib or PBKDF2 (a
         * computational expensive hash function).
         *
         * The actual identifier for PBKDF2 is pbkdf2:1000:6:sha1, which means 1000
         * iterations, 6 bytes to generate and SHA1 as pseudo-random family used for key
         * strengthening. Arguments have to be in that order, but can be reduced to
         * pbkdf2:4096 for example to override the iterations only.
         */
        'algorithm' => env('ISSO_HASH_ALGORITHM', 'pbkdf2'),
    ],

    /*
     * Provide an Atom feed for each comment thread for users to subscribe to.
     */
    'rss' => [
        /*
         * The base URL of pages is needed to build the Atom feed. By appending
         * the URI, we should get the complete URL to use to access the page
         * with the comments. When empty, Atom feeds are disabled.
         */
        'base' => env('ISSO_RSS_BASE', ''),

        /*
         * Limit the number of elements to return for each thread.
         */
        'limit' => (int) env('ISSO_RSS_LIMIT', 100)
    ]
];