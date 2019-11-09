<?php

/**
 * Session token storage
 * Class SessionStorage
 */

namespace Horoshop\RestApi\Storage;

class LaravelSessionStorage implements TokenStorageInterface
{

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key) :?string
    {
        return session($key);
    }

    /**
     * @param $key
     * @param $token
     * @return mixed
     */
    public function set(string $key, string $token)
    {
        return session([$key => $token]);
    }
}
