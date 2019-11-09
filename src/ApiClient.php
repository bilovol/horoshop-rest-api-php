<?php

/*
 * Horoshop REST API Client
 */

namespace Horoshop\RestApi;

use Exception;
use Horoshop\RestApi\Storage\FileStorage;
use Horoshop\RestApi\Storage\TokenStorageInterface;
use stdClass;

class ApiClient implements ApiInterface
{

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var FileStorage|TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $hashName;

    /**
     * @var mixed
     */
    private $token;

    /**
     * @var int
     */
    private $refreshedToken = 0;

    /**
     * ApiClient constructor.
     * @param string $domain
     * @param string $login
     * @param string $password
     * @param TokenStorageInterface $tokenStorage
     * @throws Exception
     */
    public function __construct(string $domain, string $login, string $password, TokenStorageInterface $tokenStorage)
    {
        if (empty($domain) || empty($login) || empty($password)) {
            throw new Exception('Empty params!');
        }

        $this->apiUrl = 'http://' . $domain . '/api';
        $this->login = $login;
        $this->password = $password;
        $this->tokenStorage = $tokenStorage ?? new FileStorage();
        $this->hashName = md5($domain . ':' . $login . ':' . $password);
        $this->token = $this->tokenStorage->get($this->hashName);

        if (empty($this->token) && !$this->getToken()) {
            throw new Exception('Could not connect to api');
        }
    }

    /**
     * Send request
     * @param $path
     * @param string $method
     * @param array $data
     * @param bool $useToken
     * @return stdClass
     */
    private function sendRequest($path, $method = 'GET', $data = array(), $useToken = true): stdClass
    {
        $url = $this->apiUrl . '/' . $path;

        if ($useToken && !empty($this->token)) {
            $data['token'] = $this->token;
        }

        $curl = curl_init();
        $method = strtoupper($method);

        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, count($data));
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            default:
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseBody = substr($response, $header_size);

        curl_close($curl);

        if ($headerCode === 401 && $this->refreshedToken === 0) {
            ++$this->refreshedToken;
            $this->getToken();
            $retval = $this->sendRequest($path, $method, $data);
        } else {
            $retval = new stdClass();
            $retval->data = json_decode($responseBody);
            $retval->http_code = $headerCode;
        }

        return $retval;
    }

    /**
     * Get token
     * @return bool
     */
    private function getToken(): bool
    {
        $data = [
            'login' => $this->login,
            'password' => $this->password,
        ];
        $requestResult = $this->sendRequest('auth', 'POST', $data, false);

        if ($requestResult->http_code !== 200) {
            return false;
        }

        $this->refreshedToken = 0;
        $this->token = $requestResult->data->response->token;
        $this->tokenStorage->set($this->hashName, $this->token);

        return true;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getOrderById(int $id): ?array
    {
        $ids = [$id];
        return $this->listOrders($ids);
    }

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
    public function listOrders(array $ids, array $data = []): ?array
    {
        $data['ids'] = $ids;

        $requestResult = $this->sendRequest('orders/get', 'GET', $data);

        if ($requestResult->http_code !== 200) {
            return null;
        }

        if (!empty($requestResult->data->response->orders)) {
            $orders = $requestResult->data->response->orders;
            return reset($orders);
        }

        return null;
    }

    /**
     * @param string $event
     * @param string $handlerUri
     * @return int|null
     */
    public function bind(string $event, string $handlerUri): ?int
    {
        $data['event'] = $event;
        $data['target_url'] = $handlerUri;

        $requestResult = $this->sendRequest('hooks/subscribe/', 'POST', $data);

        if ($requestResult->http_code !== 201) {
            return null;
        }

        return $requestResult->data->id;
    }

    /**
     * @param int $id
     * @param string $handlerUri
     * @return bool|null
     */
    public function unbind(int $id, string $handlerUri): ?bool
    {
        $data['id'] = $id;
        $data['target_url'] = $handlerUri;
        $requestResult = $this->sendRequest('hooks/unSubscribe/', 'POST', $data);

        return !($requestResult->http_code !== 410);
    }
}
