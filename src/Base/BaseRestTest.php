<?php

namespace PhpLab\Test\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PhpLab\Rest\Contract\Authorization\AuthorizationInterface;
use PhpLab\Rest\Contract\Authorization\BearerAuthorization;
use PhpLab\Rest\Contract\Client\RestClient;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestTest extends BaseTest
{

    protected $baseUrl;
    protected $basePath = '/';

    private $restClient;

    protected function getAuthorizationContract(Client $guzzleClient): AuthorizationInterface
    {
        return new BearerAuthorization($guzzleClient);
    }

    protected function printContent(ResponseInterface $response = null, string $filter = null) {
        $content = $response->getBody()->getContents();
        if($filter) {
            $content = $filter($content);
        }
        dd($content);
    }

    protected function getRestClient(): RestClient
    {
        $guzzleClient = $this->getGuzzleClient();
        $authAgent = $this->getAuthorizationContract($guzzleClient);
        return new RestClient($guzzleClient, $authAgent);
    }

    abstract protected function getRestAssert(ResponseInterface $response = null);

    protected function setUp(): void
    {
        parent::setUp();
        $this->setBaseUrl($_ENV['API_URL']);
    }

    /*protected function sendRequest(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }*/

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