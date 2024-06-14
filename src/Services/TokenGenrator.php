<?php

namespace App\Services;

/**
 * Description of TokenGenrator.php.
 *
 * @package App\Services
 */
class TokenGenrator
{

    /**
     * Generates new token
     *
     * @return string
     * @throws \Exception
     */
    public static function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}