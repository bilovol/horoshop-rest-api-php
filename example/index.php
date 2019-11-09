<?php

/*
 * Horoshop REST API Usage Example
 */

use Horoshop\RestApi\ApiClient;
use Horoshop\RestApi\Storage\FileStorage;

define('DOMAIN', '');
define('LOGIN', '');
define('PASSWORD', '');

try {
    $ApiClient = new ApiClient(DOMAIN, LOGIN, PASSWORD, new FileStorage());
    // get first order
    var_dump($ApiClient->getOrderById(1));
} catch (Exception $e) {
    var_dump($e->getMessage());
}

