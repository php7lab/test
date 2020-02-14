<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Domain\Entities\DataProviderEntity;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Test\Helpers\RestHelper;
use Psr\Http\Message\ResponseInterface;

class RestClient
{

    private $guzzleClient;
    private $accept = 'application/json';
    private $authToken;
    private $authUri = 'auth';

    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function authByLogin(string $login, string $password = 'Wwwqqq111') {
        $response = $this->sendPost($this->authUri, [
            'login' => $login,
            'password' => $password,
        ]);
        $this->authToken = RestHelper::getBodyAttribute($response, 'token');
        return $this;
    }

    public function setAuthToken(string $authToken) {
        $this->authToken = $authToken;
    }

    public function getAuthToken() {
        return $this->authToken;
    }

    public function sendOptions(string $uri): ResponseInterface
    {
        return $this->sendRequest(HttpMethodEnum::OPTIONS, $uri);
    }

    public function sendDelete(string $uri): ResponseInterface
    {
        return $this->sendRequest(HttpMethodEnum::DELETE, $uri);
    }

    public function sendPost(string $uri, array $body = [], string $paramName = RequestOptions::FORM_PARAMS): ResponseInterface
    {
        $options = [$paramName => $body];
        return $this->sendRequest(HttpMethodEnum::POST, $uri, $options);
    }

    public function sendPut(string $uri, array $body = [], string $paramName = RequestOptions::FORM_PARAMS): ResponseInterface
    {
        $options = [$paramName => $body];
        return $this->sendRequest(HttpMethodEnum::PUT, $uri, $options);
    }

    public function sendGet(string $uri, array $query = [], string $paramName = RequestOptions::QUERY): ResponseInterface
    {
        $options = [$paramName => $query];
        return $this->sendRequest(HttpMethodEnum::GET, $uri, $options);
    }

    public function sendRequest(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        $options[RequestOptions::HEADERS]['Accept'] = $this->accept;
        if($this->authToken) {
            $options[RequestOptions::HEADERS][HttpHeaderEnum::AUTHORIZATION] = $this->authToken;
        }
        try {
            $response = $this->guzzleClient->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

}
