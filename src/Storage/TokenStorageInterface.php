<?php

/**
 * Interface TokenStorageInterface
 */

namespace Horoshop\RestApi\Storage;

interface TokenStorageInterface
{
    /**
     * @param $key
     * @param $token
     * @return mixed
     */
    public function set(string $key, string $token);

    /**
     * @param $key
     * @return mixed
     */
    public function get(string $key);
}
