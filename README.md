#Horoshop REST client library

<img src ="http://filestorage.bitabit.com.ua/prev/Horoshop.png">

A simple Horoshop REST client library and example for PHP. 

## Installing
Via composer:
```shell
composer require horoshop/rest-api
```

## Usage
```php
<?php

/*
 * Horoshop REST API Usage Example
 */

use Horoshop\RestApi\ApiClient;
use Horoshop\RestApi\Storage\FileStorage;

define('DOMAIN', 'http://shop3316.horoshop.ua/');
define('LOGIN', 'admin');
define('PASSWORD', 'pass');

define('ORDER_ID', 1);
try {
    $ApiClient = new ApiClient(DOMAIN, LOGIN, PASSWORD, new FileStorage());
    // get first order
    var_dump($ApiClient->getOrderById(ORDER_ID));
} catch (Exception $e) {
    var_dump($e->getMessage());
}
```
## License

This project is licensed under the MIT License.
