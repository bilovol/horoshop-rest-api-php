<?php

/**
 * Horoshop ApiInterface
 */

namespace Horoshop\RestApi;

interface ApiInterface
{
    /**
     * @param int $id
     * @return array|null
     */
    public function getOrderById(int $id): ?array;

    /**
     * @param array $ids
     * @param array $data = [
     *     'from' =>!(string),
     *     'to'=>!(string),
     *     'status'=>!(int),
     *     'additionalData'=>!(bool),
     *     'limit'=>!(int),
     *     'offset'=>!(int),
     * ]
     * @return array|null
     */
    public function listOrders(array $ids, array $data = []): ?array ;

    /**
     * @param string $event
     * @param string $handlerUri
     * @return int|null
     */
    public function bind(string $event, string $handlerUri): ?int;

    /**
     * @param int $id
     * @param string $handlerUri
     * @return bool|null
     */
    public function unbind(int $id, string $handlerUri): ?bool;
}
