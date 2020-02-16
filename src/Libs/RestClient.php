<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Domain\Entities\DataProviderEntity;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Legacy\Yii\Helpers\FileHelper;
use PhpLab\Test\Helpers\RestHelper;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class RestClient
{

    private $guzzleClient;
    private $accept = 'application/json';
    private $authUri = 'auth';
    private $authCache;
    private $currentAuth = [];

    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
        $cacheDirectory = FileHelper::path($_ENV['CACHE_DIRECTORY']);
        $this->authCache = new FilesystemAdapter('test', 0, $cacheDirectory);
    }

    public function authByLogin(string $login, string $password = 'Wwwqqq111') {
        $this->currentAuth = [
            'login' => $login,
            'password' => $password,
        ];
        return $this;
    }

    public function logout() {
        $this->currentAuth = [];
    }

    public function sendOptions(string $uri, array $headers = []): ResponseInterface
    {
        $options = [
            RequestOptions::HEADERS => $headers,
        ];
        return $this->sendRequest(HttpMethodEnum::OPTIONS, $uri, $options);
    }

    public function sendDelete(string $uri, array $headers = []): ResponseInterface
    {
        $options = [
            RequestOptions::HEADERS => $headers,
        ];
        return $this->sendRequest(HttpMethodEnum::DELETE, $uri, $options);
    }

    public function sendPost(string $uri, array $body = [], array $headers = []): ResponseInterface
    {
        $options = [
            RequestOptions::FORM_PARAMS => $body,
            RequestOptions::HEADERS => $headers,
        ];
        return $this->sendRequest(HttpMethodEnum::POST, $uri, $options);
    }

    public function sendPut(string $uri, array $body = [], array $headers = []): ResponseInterface
    {
        $options = [
            RequestOptions::FORM_PARAMS => $body,
            RequestOptions::HEADERS => $headers,
        ];
        return $this->sendRequest(HttpMethodEnum::PUT, $uri, $options);
    }

    public function sendGet(string $uri, array $query = [], array $headers = []): ResponseInterface
    {
        $options = [
            RequestOptions::QUERY => $query,
            RequestOptions::HEADERS => $headers,
        ];
        return $this->sendRequest(HttpMethodEnum::GET, $uri, $options);
    }

    public function sendRequest(string $method, string $uri = '', array $options = [], bool $refreshAuthToken = true): ResponseInterface
    {
        $options[RequestOptions::HEADERS]['Accept'] = $this->accept;
        $authToken = $this->getAuthToken();
        if($authToken) {
            $options[RequestOptions::HEADERS][HttpHeaderEnum::AUTHORIZATION] = $authToken;
        } else {
            $refreshAuthToken = false;
        }
        try {
            $response = $this->guzzleClient->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if($response->getStatusCode() == HttpStatusCodeEnum::UNAUTHORIZED && $refreshAuthToken) {
                $this->authorization();
                return $this->sendRequest($method, $uri, $options, false);
            }
        }
        return $response;
    }

    private function getAuthToken(): ?string {

        if(empty($this->currentAuth['login'])) {
            return null;
        }
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->authCache->getItem('token_by_login_' . $this->currentAuth['login']);
        $authToken = $cacheItem->get();

        if($authToken) {
            return $authToken;
        } else {
            return $this->authorization();
        }
    }

    private function setAuthToken(string $authToken) {
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->authCache->getItem('token_by_login_' . $this->currentAuth['login']);
        $cacheItem->set($authToken);
        $this->authCache->save($cacheItem);
        return $this;
    }

    private function authorization() {
        $clone = clone $this;
        $clone->logout();
        $response = $clone->sendPost($this->authUri, [
            'login' => $this->currentAuth['login'],
            'password' => $this->currentAuth['password'],
        ]);
        $authToken = RestHelper::getBodyAttribute($response, 'token');
        $this->setAuthToken($authToken);
        return $authToken;
    }

}
