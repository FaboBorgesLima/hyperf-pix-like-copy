<?php

declare(strict_types=1);

use function Hyperf\Support\env;

if (! function_exists('parseKey')) {
    /**
     * Parses a key string, which can be either a direct key or a file path prefixed with 'file://'.
     *
     * @param string $key The key string to parse.
     * @return string The parsed key content.
     */
    function parseKey(string $key): string
    {
        if (str_starts_with($key, 'file://')) {
            return file_get_contents(substr($key, 7));
        }
        return $key;
    }
}

return [
    'public_key' => parseKey(env('JWT_PUBLIC_KEY', '')),
];
