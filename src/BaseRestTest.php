<?php

namespace PhpLab\Test;

use PhpLab\Domain\Data\DataProviderEntity;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use php7extension\core\web\enums\HttpHeaderEnum;
use php7extension\core\web\enums\HttpMethodEnum;
use php7extension\core\web\enums\HttpStatusCodeEnum;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseRestTest extends WebTestCase
{

    protected $baseUrl;
    protected $basePath = '/';

    protected function sendOptions($uri)
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request(HttpMethodEnum::OPTIONS, $uri);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function sendDelete($uri)
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request(HttpMethodEnum::DELETE, $uri);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function sendPost($uri, $body = [])
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request(HttpMethodEnum::POST, $uri, [
                \GuzzleHttp\RequestOptions::FORM_PARAMS => $body,
            ]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function sendPut($uri, $body = [])
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request(HttpMethodEnum::PUT, $uri, [
                \GuzzleHttp\RequestOptions::FORM_PARAMS => $body,
            ]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function sendGet($uri, $query = [])
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request(HttpMethodEnum::GET, $uri, [
                'query' => $query
            ]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function assertBody(ResponseInterface $response, $actualBody)
    {
        $body = $this->getBody($response);
        $this->assertArraySubset($actualBody, $body);
    }

    protected function getLastInsertId(ResponseInterface $response)
    {
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        return $entityId;
    }

    protected function assertCreated(ResponseInterface $response, $actualEntityId = null)
    {
        $this->assertEquals(HttpStatusCodeEnum::CREATED, $response->getStatusCode());
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        $this->assertNotEmpty($entityId);
        if($actualEntityId) {
            $this->assertEquals($actualEntityId, $entityId);
        }
    }

    protected function assertCors(ResponseInterface $response, $origin, $headers = null, $methods = null)
    {
        $actualOrigin = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_ORIGIN)[0];
        $actualHeaders = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_HEADERS)[0];
        $actualMethods = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_METHODS)[0];

        $this->assertEquals($origin, $actualOrigin);

        if($headers) {
            $this->assertEquals($headers, $actualHeaders);
        }
        if($methods) {
            $arr = explode(',', $actualMethods);
            $arr = array_map('trim', $arr);
            $diff = array_diff($methods, $arr);
            $this->assertEmpty($diff, 'Diff: ' . implode(',', $diff));
        }
    }

    protected function assertOrder($collection, $attribute, $direction= SORT_ASC)
    {
        $currentValue = null;
        foreach ($collection as $item) {
            if($currentValue === null) {
                $currentValue = $item[$attribute];
            }
            if($direction == SORT_ASC) {
                if($item[$attribute] < $currentValue) {
                    $this->expectExceptionMessage('Fail order!');
                }
                if($item[$attribute] > $currentValue) {
                    $currentValue = $item[$attribute];
                }
            } else {
                if($item[$attribute] > $currentValue) {
                    $this->expectExceptionMessage('Fail order!');
                }
                if($item[$attribute] < $currentValue) {
                    $currentValue = $item[$attribute];
                }
            }
        }
    }

    protected function assertPagination(ResponseInterface $response, $totalCount = null, $page = null, $pageSize = null)
    {
        $entity = new DataProviderEntity;
        $entity->pageSize = $response->getHeader(HttpHeaderEnum::PER_PAGE)[0];
        $entity->page = $response->getHeader(HttpHeaderEnum::CURRENT_PAGE)[0];
        $entity->totalCount = $response->getHeader(HttpHeaderEnum::TOTAL_COUNT)[0];
        //$entity->pageCount = $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0];

        if($page) {
            $this->assertEquals($page, $entity->page);
        }
        if($pageSize) {
            $this->assertEquals($pageSize, $entity->pageSize);
        }
        if($totalCount) {
            $this->assertEquals($totalCount, $entity->totalCount);
        }
        $this->assertEquals($entity->pageCount, $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0]);
    }

    protected function getBody(ResponseInterface $response)
    {
        $contentType = $response->getHeader('content-type')[0];
        $body = $response->getBody()->getContents();
        if ($contentType == 'application/json') {
            $body = \GuzzleHttp\json_decode($response->getBody(), true);
        }
        return $body;
    }

    protected function getGuzzleClient()
    {
        $client = new Client([
            'base_uri' => $this->baseUrl . '/' . $this->basePath,
        ]);
        return $client;
    }

    protected function sendRequest($uri, $method = HttpMethodEnum::GET)
    {
        $client = new Client([
            'base_uri' => $this->baseUrl . '/' . $this->basePath,
        ]);
        $request = new Request($method, $uri);
        $response = $client->send($request);
        return $response;
    }

}