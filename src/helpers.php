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