<?php

if (!function_exists('env')) {
    /**
     * Helper for getting environment variables or default on fail.
     *
     * @param string $key
     * @param null|mixed $default
     * @return array|false|null|string
     */
    function env(string $key, $default = null)
    {
        if ($e = getenv($key)) {
            return $e;
        }

        return $default;
    }
}

if (!function_exists('origin')) {
    /**
     * Return a function that returns a valid HTTP Origin or localhost
     * if none found.
     * @see https://github.com/posativ/isso/blob/5bc176d85b64eac331f578d0657ac26be40b2470/isso/wsgi.py#L78
     * @param string|null $url
     * @return string
     */
    function origin(string $url = null): string
    {
        if ($origin = env('ISSO_CORS_ORIGIN')) {
            return $origin;
        }

        if (is_null($url) || empty($url)) {
            return "http://invalid.local";
        }

        if ($origin = env('HTTP_ORIGIN', env('HTTP_REFERER'))) {
            return $origin;
        }

        return $url;
    }
}

if (!function_exists('parseTitleFromHTML')) {
    /**
     * Extract <h1> title from web page. The title is *probably* the text node,
     * which is the nearest H1 node in context to an element with the `isso-thread` id.
     *
     * @see https://github.com/posativ/isso/blob/5bc176d85b64eac331f578d0657ac26be40b2470/isso/utils/parse.py#L21
     * @param string $html
     * @return string
     */
    function parseTitleFromHTML(string $html): string
    {
        if (empty($html)) {
            return 'Untitled';
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        libxml_clear_errors();

        // If the data-title attribute is set use its value
        if ($title = $xpath->query('//*[@id="isso-thread"]/@data-title')) {
            if ($title->count() > 0 && $found = $title->item(0)->nodeValue) {
                return $found;
            }
        }

        // If the isso-thread id exists but without the data-title attribute attempt to find the nearest H1 preceding it
        if ($title = $xpath->query('//*[@id="isso-thread"]/preceding::*[self::h1 or self::h2][1]')) {
            if ($title->count() > 0 && $found = $title->item(0)->textContent)
            {
                return $found;
            }
        }

        // If else, try to use the page title.
        $title = $xpath->query('//title');
        if ($title->count() > 0 && $found = $title->item(0)->textContent) {
            return $found;
        }

        // Really?
        return 'Untitled';
    }
}