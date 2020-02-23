<?php

namespace PhpLab\Test\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PhpLab\Test\Libs\RestAssert;
use PhpLab\Test\Libs\RestClient;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestTest extends BaseTest
{

    protected $baseUrl;
    protected $basePath = '/';

    protected function getRestClient(): RestClient
    {
        $guzzleClient = $this->getGuzzleClient();
        return new RestClient($guzzleClient);
    }

    protected function getRestAssert(ResponseInterface $response = null): RestAssert
    {
        return new RestAssert($response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setBaseUrl($_ENV['API_URL']);
    }

    protected function sendRequest(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function getGuzzleClient(): Client
    {
        $baseUrl = $this->getBaseUrl();
        $config = [
            'base_uri' => $baseUrl . '/',
        ];
        $client = new Client($config);
        return $client;
    }

    protected function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    protected function getBaseUrl(): string
    {
        $basePath = trim($this->basePath, '/');
        $baseUrl = $this->baseUrl . '/' . $basePath;
        $baseUrl = trim($baseUrl, '/');
        return $baseUrl;
    }

}