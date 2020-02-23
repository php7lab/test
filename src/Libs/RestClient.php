<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Test\Interfaces\AuthAgentInterface;
use Psr\Http\Message\ResponseInterface;

class RestClient
{

    private $guzzleClient;
    private $accept = 'application/json';
    private $authAgent;

    public function __construct(Client $guzzleClient, AuthAgentInterface $authAgent = null)
    {
        $this->guzzleClient = $guzzleClient;
        $this->setAuthAgent($authAgent);
    }

    public function getAuthAgent(): AuthAgentInterface
    {
        return $this->authAgent;
    }

    public function setAuthAgent(AuthAgentInterface $authAgent = null)
    {
        $this->authAgent = $authAgent;
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
        $authToken = is_object($this->authAgent) ? $this->authAgent->getAuthToken() : null;
        if ($authToken) {
            $options[RequestOptions::HEADERS][HttpHeaderEnum::AUTHORIZATION] = $authToken;
        } else {
            $refreshAuthToken = false;
        }
        try {
            $response = $this->guzzleClient->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if (is_object($this->authAgent)) {
                if ($response->getStatusCode() == HttpStatusCodeEnum::UNAUTHORIZED && $refreshAuthToken) {
                    $this->authAgent->authorization();
                    return $this->sendRequest($method, $uri, $options, false);
                }
            }
        }
        return $response;
    }

}
