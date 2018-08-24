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

if (! function_exists('parseStringToTime')) {

    /**
     * Parse the input $string and return as time in seconds.
     * It supports years, weeks, days, hours, minutes, seconds.
     *
     * e.g. 3h45m12s equals to 3 hours, 45 minutes and 12 seconds
     * and will result in a return value equal to 13512.
     *
     * @param string $str
     * @return int
     */
    function parseStringToTime(string $str): int {
        $multipliers = [
            's' => 1,
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
            'w' => 604800,
            'y' => 31536000
        ];

        $total = 0;
        $number = '';
        for ($i = 0; $i < strlen($str); $i++){
            $s = $str[$i];
            if (strpos('0123456789smhdwy', $s) === false) {
                continue;
            }

            if (strpos('0123456789', $s) !== false) {
                $number .= $s;
                continue;
            }
            if (in_array($s, array_keys($multipliers))){
                $total += ($multipliers[$s] * (int) $number);
                $number = '';
            }
        }
        return $total;
    }
}