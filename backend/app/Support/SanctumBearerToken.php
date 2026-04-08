<?php

namespace App\Support;

class SanctumBearerToken
{
    public static function normalize(?string $token): string
    {
        if ($token === null || $token === '') {
            return '';
        }

        $token = trim($token);
        $token = trim($token, "\"' \t\n\r\0\x0B");

        return $token;
    }
}
