<?php

if (! function_exists('env')) {
    function env($key, $default = null) {
        if ($e = getenv($key)){
            return $e;
        }

        return $default;
    }
}

